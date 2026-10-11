export class ApiError extends Error {
  constructor(message, status) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
  }
}

async function request(url, options = {}) {
  const response = await fetch(url, {
    ...options,
    headers: { Accept: 'application/json', ...(options.headers ?? {}) },
  });

  const body = await response.json().catch(() => null);

  if (!response.ok) {
    throw new ApiError(body?.message ?? `Erreur ${response.status}`, response.status);
  }

  return body;
}

export function fetchVehicles(brand = null, { signal } = {}) {
  const params = new URLSearchParams();
  if (brand) params.set('brand', brand);
  const query = params.toString();

  return request(`/api/vehicles${query ? `?${query}` : ''}`, { signal });
}

export function fetchVehicle(id, { signal } = {}) {
  return request(`/api/vehicles/${encodeURIComponent(id)}`, { signal });
}

export function fetchBrands({ signal } = {}) {
  return request('/api/brands', { signal });
}
