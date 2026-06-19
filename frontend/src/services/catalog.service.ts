import api from './api';

export default {
  getCatalog(slug: string) {
    return api.get(`/public/catalog/${slug}`);
  },
  submitOrder(slug: string, payload: any) {
    return api.post(`/public/catalog/${slug}/order`, payload);
  },
  calculateShipping(slug: string, payload: {
    order_type: string;
    customer_lat?: number | null;
    customer_lng?: number | null;
    zone_name?: string | null;
    [key: string]: any;
  }) {
    return api.post(`/public/catalog/${slug}/calculate-shipping`, payload);
  },
};

