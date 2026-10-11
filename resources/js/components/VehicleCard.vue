<script setup>
import { computed, ref } from 'vue';
import { formatMileage, formatPrice } from '../format';

const props = defineProps({
  vehicle: { type: Object, required: true },
});

const emit = defineEmits(['open']);

const imageFailed = ref(false);

const name = computed(() => [props.vehicle.brand, props.vehicle.model].filter(Boolean).join(' '));

const specs = computed(() =>
  [
    props.vehicle.year,
    formatMileage(props.vehicle.mileage),
    props.vehicle.energy_label,
    props.vehicle.gearbox_label,
  ].filter(Boolean),
);

const showCatalogPrice = computed(
  () => props.vehicle.catalog_price && props.vehicle.price && props.vehicle.catalog_price > props.vehicle.price,
);
</script>

<template>
  <article class="card">
    <div class="card__media card__media--clickable" @click="emit('open', vehicle)">
      <img
        v-if="vehicle.picture && !imageFailed"
        :src="vehicle.picture"
        :alt="`Photo ${name}`"
        loading="lazy"
        decoding="async"
        @error="imageFailed = true"
      />
      <div v-else class="card__placeholder" aria-hidden="true">
        <svg viewBox="0 0 64 32" width="72" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 22h52v-5l-6-2-8-8H22l-8 8-8 2z" />
          <circle cx="18" cy="23" r="4" fill="var(--surface-muted)" />
          <circle cx="46" cy="23" r="4" fill="var(--surface-muted)" />
        </svg>
        <span>Photo indisponible</span>
      </div>
      <span v-if="vehicle.offer_type_label" class="card__badge">{{ vehicle.offer_type_label }}</span>
    </div>

    <div class="card__body">
      <p class="card__brand">{{ vehicle.brand }}</p>
      <h2 class="card__title">{{ vehicle.model ?? vehicle.title }}</h2>
      <p v-if="vehicle.version" class="card__version" :title="vehicle.version">{{ vehicle.version }}</p>

      <ul v-if="specs.length" class="card__specs">
        <li v-for="spec in specs" :key="spec">{{ spec }}</li>
      </ul>

      <div class="card__footer">
        <div>
          <p class="card__price">{{ formatPrice(vehicle.price) }}</p>
          <p v-if="showCatalogPrice" class="card__catalog">Prix neuf {{ formatPrice(vehicle.catalog_price) }}</p>
        </div>
        <button
          type="button"
          class="card__link"
          :aria-label="`Voir le détail ${name}`"
          @click="emit('open', vehicle)"
        >Voir</button>
      </div>
    </div>
  </article>
</template>
