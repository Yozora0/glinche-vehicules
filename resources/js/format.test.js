import { describe, expect, it } from 'vitest';
import { formatMileage, formatPrice, plural } from './format';

const clean = (s) => s?.replace(/[  ]/g, ' ');

describe('format', () => {
  it('formate un prix en euros sans décimales', () => {
    expect(clean(formatPrice(33500))).toBe('33 500 €');
    expect(clean(formatPrice('17990.00'))).toBe('17 990 €');
  });

  it('gère un prix absent', () => {
    expect(formatPrice(null)).toBe('Prix sur demande');
  });

  it('formate le kilométrage', () => {
    expect(clean(formatMileage(23500))).toBe('23 500 km');
    expect(formatMileage(null)).toBeNull();
  });

  it('accorde au pluriel', () => {
    expect(plural(1, 'véhicule')).toBe('1 véhicule');
    expect(plural(3, 'véhicule')).toBe('3 véhicules');
  });
});
