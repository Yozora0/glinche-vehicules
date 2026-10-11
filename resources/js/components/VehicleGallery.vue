<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
  pictures: { type: Array, default: () => [] },
  alt: { type: String, default: '' },
});

const index = ref(0);
const failed = ref(new Set());

const pictures = computed(() => props.pictures.filter((url) => !failed.value.has(url)));
const current = computed(() => pictures.value[index.value] ?? null);

watch(
  () => props.pictures,
  () => {
    index.value = 0;
    failed.value = new Set();
  },
);

function go(step) {
  const count = pictures.value.length;
  if (count > 1) index.value = (index.value + step + count) % count;
}

function onError(url) {
  failed.value = new Set(failed.value).add(url);
  if (index.value >= pictures.value.length) index.value = 0;
}

function onKeydown(event) {
  if (event.key === 'ArrowLeft') go(-1);
  if (event.key === 'ArrowRight') go(1);
}
</script>

<template>
  <div class="gallery" tabindex="0" aria-roledescription="carrousel" :aria-label="`Photos ${alt}`" @keydown="onKeydown">
    <div class="gallery__stage">
      <img v-if="current" :key="current" :src="current" :alt="`${alt} – photo ${index + 1}`" @error="onError(current)" />
      <div v-else class="card__placeholder">Photo indisponible</div>

      <template v-if="pictures.length > 1">
        <button type="button" class="gallery__nav gallery__nav--prev" aria-label="Photo précédente" @click="go(-1)">‹</button>
        <button type="button" class="gallery__nav gallery__nav--next" aria-label="Photo suivante" @click="go(1)">›</button>
        <span class="gallery__counter" aria-live="polite">{{ index + 1 }} / {{ pictures.length }}</span>
      </template>
    </div>

    <ul v-if="pictures.length > 1" class="gallery__thumbs">
      <li v-for="(url, i) in pictures" :key="url">
        <button
          type="button"
          class="gallery__thumb"
          :class="{ 'gallery__thumb--active': i === index }"
          :aria-label="`Afficher la photo ${i + 1}`"
          :aria-current="i === index"
          @click="index = i"
        >
          <img :src="url" alt="" loading="lazy" @error="onError(url)" />
        </button>
      </li>
    </ul>
  </div>
</template>
