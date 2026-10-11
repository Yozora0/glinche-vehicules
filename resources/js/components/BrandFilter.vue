<script setup>
import { plural } from '../format';

defineProps({
  brands: { type: Array, required: true },
  selected: { type: String, default: null },
  total: { type: Number, default: 0 },
});

const emit = defineEmits(['select']);
</script>

<template>
  <nav class="brand-filter" aria-label="Filtrer par marque">
    <ul class="brand-filter__list">
      <li>
        <button
          type="button"
          class="chip"
          :class="{ 'chip--active': selected === null }"
          :aria-pressed="selected === null"
          @click="emit('select', null)"
        >
          Toutes les marques
          <span class="chip__count" :aria-label="plural(total, 'véhicule')">{{ total }}</span>
        </button>
      </li>
      <li v-for="brand in brands" :key="brand.name">
        <button
          type="button"
          class="chip"
          :class="{ 'chip--active': selected === brand.name }"
          :aria-pressed="selected === brand.name"
          @click="emit('select', brand.name)"
        >
          {{ brand.name }}
          <span class="chip__count" :aria-label="plural(brand.count, 'véhicule')">{{ brand.count }}</span>
        </button>
      </li>
    </ul>
  </nav>
</template>
