<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { fetchVehicle } from '../api';
import { formatMileage, formatPrice } from '../format';
import VehicleGallery from './VehicleGallery.vue';

const props = defineProps({
  vehicle: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const cache = new Map();
const dialog = ref(null);
const detail = ref(null);
const loading = ref(false);
const error = ref(null);
let controller = null;

const data = computed(() => detail.value ?? props.vehicle ?? {});
const name = computed(() => [data.value.brand, data.value.model].filter(Boolean).join(' '));
const pictures = computed(() => detail.value?.pictures ?? (props.vehicle?.picture ? [props.vehicle.picture] : []));

const highlights = computed(() =>
  [
    { label: 'Année', value: data.value.year },
    { label: 'Kilométrage', value: formatMileage(data.value.mileage) },
    { label: 'Énergie', value: data.value.energy_label },
    { label: 'Boîte', value: data.value.gearbox_label },
    { label: 'Puissance', value: data.value.power ? `${data.value.power} ch` : null },
  ].filter((h) => h.value),
);

const showCatalogPrice = computed(
  () => data.value.catalog_price && data.value.price && data.value.catalog_price > data.value.price,
);

const equipmentCount = computed(() =>
  (detail.value?.equipments ?? []).reduce((sum, group) => sum + group.items.length, 0),
);

async function load(id) {
  error.value = null;

  if (cache.has(id)) {
    detail.value = cache.get(id);
    return;
  }

  detail.value = null;
  controller?.abort();
  controller = new AbortController();
  loading.value = true;

  try {
    const result = await fetchVehicle(id, { signal: controller.signal });
    cache.set(id, result);
    if (props.vehicle?.id === id) detail.value = result;
  } catch (e) {
    if (e.name !== 'AbortError') error.value = e.message || 'Impossible de charger le détail.';
  } finally {
    loading.value = false;
  }
}

function open() {
  const el = dialog.value;
  if (!el || el.open) return;
  typeof el.showModal === 'function' ? el.showModal() : el.setAttribute('open', '');
  document.body.style.overflow = 'hidden';
}

function close() {
  emit('close');
}

watch(
  () => props.vehicle?.id,
  async (id) => {
    if (id == null) {
      controller?.abort();
      if (dialog.value?.open) typeof dialog.value.close === 'function' ? dialog.value.close() : dialog.value.removeAttribute('open');
      document.body.style.overflow = '';
      return;
    }
    await nextTick();
    open();
    dialog.value?.querySelector('.modal__body')?.scrollTo?.(0, 0);
    load(id);
  },
  { immediate: true },
);

function onBackdropClick(event) {
  if (event.target === dialog.value) close();
}

onBeforeUnmount(() => {
  controller?.abort();
  document.body.style.overflow = '';
});
</script>

<template>
  <dialog
    ref="dialog"
    class="modal"
    :aria-labelledby="vehicle ? 'modal-title' : undefined"
    @cancel.prevent="close"
    @click="onBackdropClick"
  >
    <div v-if="vehicle" class="modal__panel">
      <header class="modal__header">
        <div class="modal__heading">
          <p class="card__brand">{{ data.brand }}</p>
          <h2 id="modal-title" class="modal__title">{{ data.model ?? data.title }}</h2>
          <p v-if="data.version" class="modal__version">{{ data.version }}</p>
        </div>
        <button type="button" class="modal__close" aria-label="Fermer" @click="close">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </header>

      <div class="modal__body">
        <div class="modal__top">
          <VehicleGallery :pictures="pictures" :alt="name" />

          <aside class="modal__summary">
            <span v-if="data.offer_type_label" class="modal__badge">{{ data.offer_type_label }}</span>
            <p class="modal__price">{{ formatPrice(data.price) }}</p>
            <p v-if="showCatalogPrice" class="card__catalog">Prix neuf {{ formatPrice(data.catalog_price) }}</p>
            <p v-if="detail?.price_notes?.length" class="modal__notes">{{ detail.price_notes.join(' · ') }}</p>

            <dl v-if="highlights.length" class="modal__highlights">
              <div v-for="h in highlights" :key="h.label">
                <dt>{{ h.label }}</dt>
                <dd>{{ h.value }}</dd>
              </div>
            </dl>
          </aside>
        </div>

        <div v-if="loading" class="modal__loading" role="status">
          <span class="spinner" aria-hidden="true" /> Chargement de la fiche complète…
        </div>

        <div v-else-if="error" class="state state--error" role="alert">
          <p class="state__title">Détail indisponible</p>
          <p>{{ error }}</p>
          <button type="button" class="button" @click="load(vehicle.id)">Réessayer</button>
        </div>

        <template v-else-if="detail">
          <div v-if="detail.sections.length" class="modal__sections">
            <section v-for="section in detail.sections" :key="section.title" class="spec">
              <h3 class="spec__title">{{ section.title }}</h3>
              <dl class="spec__list">
                <div v-for="item in section.items" :key="item.label" class="spec__row">
                  <dt>{{ item.label }}</dt>
                  <dd>{{ item.value }}</dd>
                </div>
              </dl>
            </section>
          </div>

          <section v-if="detail.options.length" class="modal__block">
            <h3 class="spec__title">Options ({{ detail.options.length }})</h3>
            <ul class="feature-list">
              <li v-for="option in detail.options" :key="option.name">
                <span class="feature-list__name">{{ option.name }}</span>
                <span v-if="option.price" class="feature-list__price">{{ option.price }}</span>
                <span v-if="option.details.length" class="feature-list__details">{{ option.details.join(', ') }}</span>
              </li>
            </ul>
          </section>

          <section v-if="detail.equipments.length" class="modal__block">
            <h3 class="spec__title">Équipements de série ({{ equipmentCount }})</h3>
            <details v-for="(group, i) in detail.equipments" :key="group.category" class="equipment" :open="i === 0">
              <summary>
                {{ group.category }}
                <span class="chip__count">{{ group.items.length }}</span>
              </summary>
              <ul class="feature-list">
                <li v-for="item in group.items" :key="item.name">
                  <span class="feature-list__name">{{ item.name }}</span>
                  <span v-if="item.details.length" class="feature-list__details">{{ item.details.join(', ') }}</span>
                </li>
              </ul>
            </details>
          </section>

          <p v-if="!detail.sections.length && !detail.options.length && !detail.equipments.length" class="modal__empty">
            Aucune information complémentaire n'est fournie pour ce véhicule.
          </p>
        </template>
      </div>
    </div>
  </dialog>
</template>
