<script setup>
import { computed, onMounted, ref } from 'vue';
import BrandFilter from './components/BrandFilter.vue';
import VehicleCard from './components/VehicleCard.vue';
import VehicleModal from './components/VehicleModal.vue';
import { useVehicles } from './composables/useVehicles';
import { plural } from './format';

const { brands, vehicles, selectedBrand, loading, error, totalCount, init, loadVehicles, retry } = useVehicles();

const SORTS = {
  default: { label: 'Marque / modèle', compare: null },
  'price-asc': { label: 'Prix croissant', compare: (a, b) => nullsLast(a.price, b.price) },
  'price-desc': { label: 'Prix décroissant', compare: (a, b) => nullsLast(b.price, a.price, true) },
  'mileage-asc': { label: 'Kilométrage croissant', compare: (a, b) => nullsLast(a.mileage, b.mileage) },
  'year-desc': { label: 'Plus récents', compare: (a, b) => nullsLast(b.year, a.year, true) },
};

function nullsLast(x, y, reversed = false) {
  if (x == null && y == null) return 0;
  if (x == null) return reversed ? -1 : 1;
  if (y == null) return reversed ? 1 : -1;
  return x - y;
}

const sort = ref('default');

const openedVehicle = ref(null);

const sortedVehicles = computed(() => {
  const compare = SORTS[sort.value]?.compare;
  return compare ? [...vehicles.value].sort(compare) : vehicles.value;
});

const resultLabel = computed(() => {
  const count = plural(vehicles.value.length, 'véhicule');
  return selectedBrand.value ? `${count} ${selectedBrand.value}` : `${count} disponible${vehicles.value.length > 1 ? 's' : ''}`;
});

function selectBrand(brand) {
  const url = new URL(window.location.href);
  brand ? url.searchParams.set('marque', brand) : url.searchParams.delete('marque');
  window.history.replaceState(null, '', url);
  loadVehicles(brand);
}

onMounted(() => {
  init(new URL(window.location.href).searchParams.get('marque'));
});
</script>

<template>
  <header class="header">
    <div class="container header__inner">
      <p class="header__logo">Glinche<span>Automobiles</span></p>
      <p class="header__tagline">Véhicules disponibles</p>
    </div>
  </header>

  <main class="container main">
    <div class="intro">
      <h1>Trouvez votre prochain véhicule</h1>
      <p v-if="!error" class="intro__count" aria-live="polite">
        <template v-if="loading && !vehicles.length">Chargement des véhicules…</template>
        <template v-else>{{ resultLabel }}</template>
      </p>
    </div>

    <div v-if="brands.length" class="toolbar">
      <BrandFilter :brands="brands" :selected="selectedBrand" :total="totalCount" @select="selectBrand" />

      <label class="sort">
        <span>Trier par</span>
        <select v-model="sort">
          <option v-for="(option, key) in SORTS" :key="key" :value="key">{{ option.label }}</option>
        </select>
      </label>
    </div>

    <div v-if="error" class="state state--error" role="alert">
      <p class="state__title">Impossible d'afficher les véhicules</p>
      <p>{{ error }}</p>
      <button type="button" class="button" @click="retry">Réessayer</button>
    </div>

    <ul v-else-if="loading && !vehicles.length" class="grid" aria-hidden="true">
      <li v-for="n in 8" :key="n" class="card card--skeleton">
        <div class="card__media" />
        <div class="card__body">
          <span class="skeleton skeleton--sm" />
          <span class="skeleton skeleton--lg" />
          <span class="skeleton" />
          <span class="skeleton skeleton--price" />
        </div>
      </li>
    </ul>

    <div v-else-if="!vehicles.length" class="state">
      <p class="state__title">Aucun véhicule disponible</p>
      <p v-if="selectedBrand">Aucun véhicule {{ selectedBrand }} pour le moment.</p>
      <button v-if="selectedBrand" type="button" class="button" @click="selectBrand(null)">Voir toutes les marques</button>
    </div>

    <ul v-else class="grid" :class="{ 'grid--loading': loading }" :aria-busy="loading">
      <li v-for="vehicle in sortedVehicles" :key="vehicle.id">
        <VehicleCard :vehicle="vehicle" @open="openedVehicle = $event" />
      </li>
    </ul>
  </main>

  <VehicleModal :vehicle="openedVehicle" @close="openedVehicle = null" />

  <footer class="footer">
    <div class="container">Données fournies par l'API partenaires Glinche Automobiles.</div>
  </footer>
</template>
