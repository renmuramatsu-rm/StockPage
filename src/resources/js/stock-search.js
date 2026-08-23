function initStockSearch(root) {
    const input = root.querySelector('[data-stock-search-input]');
    const hidden = root.querySelector('[data-stock-search-value]');
    const list = root.querySelector('[data-stock-search-list]');
    const dataEl = root.querySelector('[data-stock-search-data]');
    const stocks = JSON.parse(dataEl.textContent);

    function render(items) {
        list.innerHTML = '';

        if (items.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'px-3 py-2 text-sm text-gray-400';
            empty.textContent = '該当する銘柄がありません';
            list.appendChild(empty);
            list.classList.remove('hidden');
            return;
        }

        items.slice(0, 30).forEach((s) => {
            const row = document.createElement('div');
            row.className = 'px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm flex justify-between gap-3';
            row.innerHTML = `<span class="text-gray-900">${s.name}</span><span class="text-gray-400 font-mono">${s.code}</span>`;
            row.addEventListener('mousedown', (e) => {
                e.preventDefault();
                hidden.value = s.code;
                hidden.dispatchEvent(new Event('change'));
                input.value = `${s.code} - ${s.name}`;
                list.classList.add('hidden');
            });
            list.appendChild(row);
        });

        list.classList.remove('hidden');
    }

    function search() {
        const q = input.value.trim().toLowerCase();
        hidden.value = '';

        if (!q) {
            list.classList.add('hidden');
            return;
        }

        const filtered = stocks.filter(
            (s) => s.code.toLowerCase().includes(q) || s.name.toLowerCase().includes(q)
        );
        render(filtered);
    }

    input.addEventListener('input', search);
    input.addEventListener('focus', () => {
        if (input.value.trim()) search();
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) {
            list.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-stock-search]').forEach(initStockSearch);
});
