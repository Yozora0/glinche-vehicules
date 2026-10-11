const priceFormatter = new Intl.NumberFormat('fr-FR', {
  style: 'currency',
  currency: 'EUR',
  maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('fr-FR');

export function formatPrice(value) {
  if (value === null || value === undefined || Number.isNaN(Number(value))) {
    return 'Prix sur demande';
  }
  return priceFormatter.format(Number(value));
}

export function formatMileage(value) {
  if (value === null || value === undefined) return null;
  return `${numberFormatter.format(Number(value))} km`;
}

export function plural(count, word) {
  return `${numberFormatter.format(count)} ${word}${count > 1 ? 's' : ''}`;
}
