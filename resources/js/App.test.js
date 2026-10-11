import { afterEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import App from './App.vue';

const vehicles = [
  { id: 1, brand: 'Renault', model: 'Austral', version: 'E-Tech 200ch Techno', year: 2024, mileage: 50, energy_label: 'Hybride essence', gearbox_label: 'Automatique', offer_type_label: '0 km', price: 33500, catalog_price: 42950, picture: 'https://example.com/a.jpg', url: null },
  { id: 2, brand: 'Peugeot', model: '208', version: '1.2 PureTech', year: 2022, mileage: 23500, energy_label: 'Essence', price: null, picture: null },
];

function stubApi() {
  vi.stubGlobal('fetch', vi.fn(async (url) => {
    const u = new URL(url, 'http://localhost');
    const brand = u.searchParams.get('brand');
    const detailMatch = u.pathname.match(/^\/api\/vehicles\/(\d+)$/);
    let body;
    if (detailMatch) {
      body = {
        ...vehicles.find((v) => v.id === Number(detailMatch[1])),
        pictures: ['https://example.com/a.jpg', 'https://example.com/b.jpg'],
        price_notes: ['TVA 20 %'],
        sections: [{ title: 'Motorisation', items: [{ label: 'Cylindrée', value: '1 199 cm³' }] }],
        options: [{ name: 'Peinture métallisée', price: '680 € HT', details: [] }],
        equipments: [{ category: 'Sécurité', items: [{ name: 'Régulateur adaptatif', details: [] }] }],
      };
    } else if (u.pathname === '/api/brands') {
      body = { data: [{ name: 'Peugeot', count: 1 }, { name: 'Renault', count: 1 }] };
    } else {
      body = { data: vehicles.filter((v) => !brand || v.brand === brand) };
    }
    return { ok: true, status: 200, json: async () => body };
  }));
}

afterEach(() => vi.unstubAllGlobals());

describe('App', () => {
  it('affiche les véhicules et filtre au clic sur une marque', async () => {
    stubApi();
    const wrapper = mount(App);
    await flushPromises();

    expect(wrapper.findAll('article.card')).toHaveLength(2);
    expect(wrapper.text()).toContain('Toutes les marques');
    expect(wrapper.text()).toContain('Prix sur demande');
    expect(wrapper.text()).toContain('Photo indisponible');

    const renaultChip = wrapper.findAll('button.chip').find((b) => b.text().startsWith('Renault'));
    await renaultChip.trigger('click');
    await flushPromises();

    expect(wrapper.findAll('article.card')).toHaveLength(1);
    expect(wrapper.text()).toContain('Austral');
    expect(renaultChip.attributes('aria-pressed')).toBe('true');
    expect(window.location.search).toBe('?marque=Renault');
  });

  it('ouvre la fiche détail dans une modale au clic sur "Voir"', async () => {
    stubApi();
    const wrapper = mount(App, { attachTo: document.body });
    await flushPromises();

    const dialog = wrapper.find('dialog.modal');
    expect(dialog.attributes('open')).toBeUndefined();

    await wrapper.find('article.card button.card__link').trigger('click');
    await flushPromises();

    expect(dialog.attributes('open')).toBeDefined();
    expect(fetch).toHaveBeenCalledWith('/api/vehicles/1', expect.anything());
    expect(dialog.text()).toContain('Austral');
    expect(dialog.text()).toContain('Cylindrée');
    expect(dialog.text()).toContain('680 € HT');
    expect(dialog.text()).toContain('Régulateur adaptatif');
    expect(dialog.text()).toContain('1 / 2');

    await dialog.find('.modal__close').trigger('click');
    await flushPromises();
    expect(dialog.attributes('open')).toBeUndefined();

    wrapper.unmount();
  });
});
