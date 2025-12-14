import apiClient from './api';

export const customerService = {
    // Get semua customers
    getAll: () => apiClient.get('/customers'),

    // Get detail customer
    getById: (id) => apiClient.get(`/customers/${id}`),

    // Create customer baru
    create: (data) => apiClient.post('/customers', data),

    // Create customer dengan file uploads
    createWithFiles: (formData) => apiClient.post('/customers', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    }),

    // Update customer
    update: (id, data) => apiClient.post(`/customers/${id}`, data),

    // Update customer dengan file uploads
    updateWithFiles: (id, formData) => apiClient.post(`/customers/${id}`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    }),

    // Delete customer
    delete: (id) => apiClient.delete(`/customers/${id}`),

    // Get riwayat pembayaran customer
    getPaymentHistory: (id) => apiClient.get(`/customers/${id}/riwayat`),
};

export default customerService;
