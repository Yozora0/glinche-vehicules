import { afterEach, describe, expect, it, vi } from 'vitest';
import { useVehicles } from './useVehicles';

const renault = { id: 1, brand: 'Renault', model: 'Clio' };
const peugeot = { id: 2, brand: 'Peugeot', model: '208' };

function mockApi() {
  const fetchMock = vi.fn(async (url) => {
    const u = new URL(url, 'http://localhost');
    let body;
    if (u.pathname === '/api/brands') {
      body = { data: [{ name: 'Peugeot', count: 1 }, { name: 'Renault', count: 1 }] };
    } else {
      const brand = u.searchParams.get('brand');
      const data = [renault, peugeot].filter((v) => !brand || v.brand.toLowerCase() === brand.toLowerCase());
      body = { data, meta: { count: data.length } };
    }
    return { ok: true, status: 200, json: async () => body };
  });
  vi.stubGlobal('fetch', fetchMock);
  return fetchMock;
}

afterEach(() => vi.unstubAllGlobals());

describe('useVehicles', () => {
  it('charge les véhicules puis les marques', async () => {
    mockApi();
    const state = useVehicles();
    await state.init();

    expect(state.vehicles.value).toHaveLength(2);
    expect(state.brands.value.map((b) => b.name)).toEqual(['Peugeot', 'Renault']);
    expect(state.totalCount.value).toBe(2);
    expect(state.loading.value).toBe(false);
  });

  it('filtre par marque et réutilise le cache', async () => {
    const fetchMock = mockApi();
    const state = useVehicles();
    await state.init();

    await state.loadVehicles('Renault');
    expect(state.vehicles.value).toEqual([renault]);

    const calls = fetchMock.mock.calls.length;
    await state.loadVehicles(null);
    await state.loadVehicles('Renault');
    expect(fetchMock.mock.calls.length).toBe(calls);
  });

  it('normalise la marque venant de l\'URL', async () => {
    mockApi();
    const state = useVehicles();
    await state.init('renault');

    expect(state.selectedBrand.value).toBe('Renault');
    expect(state.vehicles.value).toEqual([renault]);
  });

  it('revient à toutes les marques si la marque de l\'URL est inconnue', async () => {
    mockApi();
    const state = useVehicles();
    await state.init('Ferrari');

    expect(state.selectedBrand.value).toBeNull();
    expect(state.vehicles.value).toHaveLength(2);
  });

  it('expose le message d\'erreur du serveur', async () => {
    vi.stubGlobal('fetch', vi.fn(async () => ({
      ok: false,
      status: 502,
      json: async () => ({ message: 'API indisponible' }),
    })));
    const state = useVehicles();
    await state.init();

    expect(state.error.value).toBe('API indisponible');
    expect(state.loading.value).toBe(false);
  });
});
