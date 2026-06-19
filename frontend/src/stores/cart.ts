import { defineStore } from 'pinia';
import catalogService from '@/services/catalog.service';
import type { PriceTier } from '@/stores/catalog';

export interface CartItem {
  product_id: number;
  name: string;
  base_price: number;       // harga dasar produk (price fallback)
  price_tiers: PriceTier[]; // tier yang disimpan saat produk ditambahkan
  unit_price: number;       // harga satuan saat ini (resolved dari tier)
  quantity: number;
}

type OrderType = 'delivery' | 'pickup';
type PaymentPreference = 'cash' | 'transfer' | 'qris' | 'tempo' | 'credit';

/**
 * Resolve the applicable unit_price for a given quantity, based on price tiers.
 * Falls back to basePrice if no tiers defined or no matching tier.
 */
export function resolveUnitPrice(tiers: PriceTier[], basePrice: number, qty: number): number {
  if (!tiers || tiers.length === 0) return basePrice;
  for (const tier of tiers) {
    const minOk = qty >= tier.min_qty;
    const maxOk = tier.max_qty === null || qty <= tier.max_qty;
    if (minOk && maxOk) return Number(tier.unit_price);
  }
  return basePrice;
}

function toNumber(value: unknown, fallback = 0): number {
  const numberValue = typeof value === 'number' ? value : parseFloat(String(value ?? ''));
  return Number.isFinite(numberValue) ? numberValue : fallback;
}

function normalizeCartItem(item: Partial<CartItem> & { price?: string | number }): CartItem | null {
  const productId = toNumber(item.product_id);
  if (!productId) return null;

  const quantity = Math.max(1, toNumber(item.quantity, 1));
  const tiers = Array.isArray(item.price_tiers) ? item.price_tiers : [];
  const basePrice = toNumber(item.base_price, toNumber(item.price, toNumber(item.unit_price)));
  const unitPrice = toNumber(item.unit_price, resolveUnitPrice(tiers, basePrice, quantity));

  return {
    product_id: productId,
    name: item.name || '-',
    base_price: basePrice,
    price_tiers: tiers,
    unit_price: unitPrice,
    quantity,
  };
}

function defaultCartState() {
  return {
    items: [] as CartItem[],
    customerName: '',
    customerWhatsapp: '',
    customerAddress: '',
    locationLink: '',
    customerLat: null as number | null,
    customerLng: null as number | null,
    selectedZone: '',
    notes: '',
    orderType: 'delivery' as OrderType,
    paymentPreference: 'cash' as PaymentPreference,
  };
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    tenantSlug: null as string | null,
    ...defaultCartState(),
    // Shipping calculation state
    shippingCost: 0 as number,
    shippingDistanceKm: null as number | null,
    shippingZoneResult: null as string | null,
    shippingMode: 'none' as string,
    isCalculatingShipping: false,
    shippingError: null as string | null,
    // Checkout state
    checkoutResult: null as {
      order_id: number;
      invoice_number: string;
      whatsapp_redirect_url: string;
      shipping_cost?: number;
      shipping_distance_km?: number | null;
      shipping_zone?: string | null;
      grand_total?: number;
    } | null,
    isSubmitting: false,
    checkoutError: null as string | null,
  }),
  getters: {
    totalItemsCount: (state) => state.items.reduce((acc, item) => acc + item.quantity, 0),
    /** Subtotal: each item uses its resolved unit_price (tier-aware) */
    totalPrice: (state) => state.items.reduce((acc, item) => acc + toNumber(item.unit_price, item.base_price) * item.quantity, 0),
    /** Grand total = subtotal + shipping */
    grandTotal: (state) => {
      const subtotal = state.items.reduce((acc, item) => acc + toNumber(item.unit_price, item.base_price) * item.quantity, 0);
      return subtotal + (state.shippingCost || 0);
    },
  },
  actions: {
    storageKey(slug?: string | null) {
      const actualSlug = slug !== undefined ? slug : this.tenantSlug;
      return actualSlug ? `cart:${actualSlug}` : 'cart:unknown';
    },
    useTenant(slug: string) {
      if (this.tenantSlug === slug) return;

      this.tenantSlug = slug;
      const savedCart = JSON.parse(localStorage.getItem(this.storageKey(slug)) || 'null');
      const nextState = savedCart || defaultCartState();

      this.items             = (nextState.items || []).map(normalizeCartItem).filter(Boolean) as CartItem[];
      this.customerName      = nextState.customerName || '';
      this.customerWhatsapp  = nextState.customerWhatsapp || '';
      this.customerAddress   = nextState.customerAddress || '';
      this.locationLink      = nextState.locationLink || '';
      this.customerLat       = nextState.customerLat ?? null;
      this.customerLng       = nextState.customerLng ?? null;
      this.selectedZone      = nextState.selectedZone || '';
      this.notes             = nextState.notes || '';
      this.orderType         = nextState.orderType || 'delivery';
      this.paymentPreference = nextState.paymentPreference || 'cash';
      // Reset shipping on tenant change
      this.shippingCost       = 0;
      this.shippingDistanceKm = null;
      this.shippingZoneResult = null;
      this.shippingMode       = 'none';
      this.checkoutResult     = null;
      this.checkoutError      = null;
    },
    persist() {
      if (!this.tenantSlug) return;

      localStorage.setItem(this.storageKey(), JSON.stringify({
        items: this.items,
        customerName: this.customerName,
        customerWhatsapp: this.customerWhatsapp,
        customerAddress: this.customerAddress,
        locationLink: this.locationLink,
        customerLat: this.customerLat,
        customerLng: this.customerLng,
        selectedZone: this.selectedZone,
        notes: this.notes,
        orderType: this.orderType,
        paymentPreference: this.paymentPreference,
      }));
    },
    addToCart(product: { id: number; name: string; price: string; price_tiers?: PriceTier[] }, qty: number = 1) {
      const basePrice = parseFloat(product.price);
      const tiers     = product.price_tiers ?? [];
      const existing  = this.items.find(i => i.product_id === product.id);

      if (existing) {
        existing.quantity  += qty;
        // Recalculate unit_price for new quantity
        existing.base_price = toNumber(existing.base_price, basePrice);
        existing.price_tiers = Array.isArray(existing.price_tiers) ? existing.price_tiers : [];
        existing.unit_price = resolveUnitPrice(existing.price_tiers, existing.base_price, existing.quantity);
      } else {
        const unitPrice = resolveUnitPrice(tiers, basePrice, qty);
        this.items.push({
          product_id:  product.id,
          name:        product.name,
          base_price:  basePrice,
          price_tiers: tiers,
          unit_price:  unitPrice,
          quantity:    qty,
        });
      }
      this.persist();
    },
    removeFromCart(productId: number) {
      this.items = this.items.filter(i => i.product_id !== productId);
      this.persist();
    },
    updateQuantity(productId: number, qty: number) {
      const item = this.items.find(i => i.product_id === productId);
      if (item) {
        item.quantity   = Math.max(1, qty);
        // Recalculate unit_price whenever quantity changes
        item.base_price = toNumber(item.base_price, toNumber(item.unit_price));
        item.price_tiers = Array.isArray(item.price_tiers) ? item.price_tiers : [];
        item.unit_price = resolveUnitPrice(item.price_tiers, item.base_price, item.quantity);
      }
      this.persist();
    },
    clearCart() {
      const nextState = defaultCartState();

      this.items            = nextState.items;
      this.customerName     = nextState.customerName;
      this.customerWhatsapp = nextState.customerWhatsapp;
      this.customerAddress  = nextState.customerAddress;
      this.locationLink     = nextState.locationLink;
      this.customerLat      = null;
      this.customerLng      = null;
      this.selectedZone     = '';
      this.notes            = nextState.notes;
      this.orderType        = nextState.orderType;
      this.paymentPreference = nextState.paymentPreference;
      this.shippingCost     = 0;
      this.shippingDistanceKm = null;
      this.shippingZoneResult = null;
      this.checkoutResult   = null;
      this.persist();
    },

    /** Call backend to calculate real-time shipping cost */
    async calculateShipping(slug: string) {
      if (this.orderType === 'pickup') {
        this.shippingCost       = 0;
        this.shippingDistanceKm = null;
        this.shippingZoneResult = null;
        this.shippingMode       = 'none';
        return;
      }

      this.isCalculatingShipping = true;
      this.shippingError = null;

      try {
        const payload: Record<string, any> = {
          order_type: this.orderType,
        };

        if (this.customerLat && this.customerLng) {
          payload.customer_lat = this.customerLat;
          payload.customer_lng = this.customerLng;
        }

        if (this.selectedZone) {
          payload.zone_name = this.selectedZone;
        }

        const response = await catalogService.calculateShipping(slug, payload as any);

        if (response.data.status === 'success') {
          const data = response.data.data;
          this.shippingCost       = data.cost ?? 0;
          this.shippingDistanceKm = data.distance_km ?? null;
          this.shippingZoneResult = data.zone ?? null;
          this.shippingMode       = data.mode ?? 'none';
        }
      } catch (err: any) {
        this.shippingError = err.response?.data?.message || 'Gagal menghitung ongkir';
        this.shippingCost  = 0;
      } finally {
        this.isCalculatingShipping = false;
      }
    },

    async checkout(slug: string) {
      this.useTenant(slug);
      this.isSubmitting  = true;
      this.checkoutError = null;
      try {
        const payload: Record<string, any> = {
          customer_name:     this.customerName,
          customer_whatsapp: this.customerWhatsapp,
          customer_address:  this.customerAddress,
          location_link:     this.locationLink || null,
          notes:             this.notes,
          order_type:        this.orderType,
          payment_preference: this.paymentPreference,
          items: this.items.map(i => ({
            product_id: i.product_id,
            quantity:   i.quantity,
          })),
        };

        // Attach shipping data based on mode
        if (this.customerLat && this.customerLng) {
          payload.customer_lat = this.customerLat;
          payload.customer_lng = this.customerLng;
        }
        if (this.selectedZone) {
          payload.shipping_zone = this.selectedZone;
        }

        const response = await catalogService.submitOrder(slug, payload);
        if (response.data.status === 'success') {
          const resultData = response.data.data;
          this.clearCart();
          this.checkoutResult = resultData;
          this.persist();
          return resultData;
        } else {
          this.checkoutError = response.data.message || 'Gagal mengirim pesanan';
        }
      } catch (err: any) {
        this.checkoutError = err.response?.data?.message || 'Terjadi kesalahan koneksi';
      } finally {
        this.isSubmitting = false;
      }
      return null;
    },
  },
});
