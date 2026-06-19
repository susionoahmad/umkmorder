import { defineStore } from 'pinia';
import catalogService from '@/services/catalog.service';

export interface ShippingZone {
  name: string;
  cost: number;
}

export interface ShippingDistance {
  max_km: number;
  cost: number;
}

export interface Tenant {
  name: string;
  slug: string;
  logo: string | null;
  phone: string;
  address: string | null;
  settings: {
    catalog_title: string;
    catalog_description: string | null;
    catalog_banner: string | null;
    catalog_enabled: boolean;
    auto_whatsapp_redirect: boolean;
    show_price: boolean;
    theme: string;
    // Shipping fields
    shipping_mode: 'none' | 'zone' | 'distance' | 'api';
    store_latitude: number | null;
    store_longitude: number | null;
    store_location_label: string | null;
    shipping_zones: ShippingZone[] | null;
    shipping_distances: ShippingDistance[] | null;
  };
}

export interface PriceTier {
  min_qty: number;
  max_qty: number | null;
  unit_price: number;
}

export interface Product {
  id: number;
  name: string;
  sku: string | null;
  price: string;
  description: string | null;
  image_url: string | null;
  show_image: boolean;
  price_tiers: PriceTier[];
}

export const useCatalogStore = defineStore('catalog', {
  state: () => ({
    tenant: null as Tenant | null,
    products: [] as Product[],
    isLoading: false,
    error: null as string | null,
  }),
  actions: {
    async fetchCatalog(slug: string) {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await catalogService.getCatalog(slug);
        if (response.data.status === 'success') {
          this.tenant = response.data.data.tenant;
          this.products = response.data.data.products;
        } else {
          this.error = response.data.message || 'Gagal memuat katalog';
        }
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Terjadi kesalahan koneksi';
      } finally {
        this.isLoading = false;
      }
    },
  },
});
