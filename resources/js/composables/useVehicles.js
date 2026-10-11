import { computed, ref, shallowRef } from 'vue';
import { fetchBrands, fetchVehicles } from '../api';

export function useVehicles() {
  const brands = ref([]);
  const vehicles = shallowRef([]);
  const selectedBrand = ref(null);
  const loading = ref(false);
  const error = ref(null);

  const cache = new Map();
  let controller = null;

  const totalCount = computed(() => brands.value.reduce((sum, b) => sum + b.count, 0));

  async function loadVehicles(brand) {
    const key = brand ?? '';
    selectedBrand.value = brand;
    error.value = null;

    if (cache.has(key)) {
      controller?.abort();
      loading.value = false;
      vehicles.value = cache.get(key);
      return;
    }

    controller?.abort();
    controller = new AbortController();
    loading.value = true;

    try {
      const { data } = await fetchVehicles(brand, { signal: controller.signal });
      cache.set(key, data);
      vehicles.value = data;
      loading.value = false;
    } catch (e) {
      if (e.name === 'AbortError') return;
      error.value = e.message || 'Une erreur est survenue.';
      vehicles.value = [];
      loading.value = false;
    }
  }

  async function init(initialBrand = null) {
    loading.value = true;
    error.value = null;

    try {
      await loadVehicles(initialBrand);
      if (error.value) return;

      const { data } = await fetchBrands();
      brands.value = data;

      if (initialBrand) {
        const match = data.find((b) => b.name.toLowerCase() === initialBrand.toLowerCase());
        if (match) {
          cache.set(match.name, vehicles.value);
          selectedBrand.value = match.name;
        } else {
          await loadVehicles(null);
        }
      }
    } catch (e) {
      error.value = e.message || 'Une erreur est survenue.';
      loading.value = false;
    }
  }

  function retry() {
    cache.clear();
    return init(selectedBrand.value);
  }

  return { brands, vehicles, selectedBrand, loading, error, totalCount, init, loadVehicles, retry };
}
