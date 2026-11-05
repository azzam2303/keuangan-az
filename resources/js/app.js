import './bootstrap';

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const input = document.getElementById(toggle.getAttribute('aria-controls'));

        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!isVisible));
        toggle.setAttribute('aria-label', isVisible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        toggle.querySelector('[data-eye-slash]')?.toggleAttribute('hidden', isVisible);
    });
});

const formatRupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(Number(amount)))}`;

const updateTransactionChart = (data) => {
    const chart = document.querySelector('[data-chart-maximum]');
    if (!(chart instanceof SVGSVGElement)) {
        return;
    }

    const maximum = Number(chart.dataset.chartMaximum);
    const points = [...chart.querySelectorAll('[data-chart-date]')];
    const line = points.map((point, index) => {
        const amount = Number(data.dailyTotals[point.dataset.chartDate] ?? 0);
        const x = Number(point.getAttribute('cx'));
        const y = 238 - (amount / maximum) * 216;

        point.setAttribute('cy', y.toFixed(1));
        point.dataset.chartAmount = String(amount);

        const title = point.querySelector('title');
        if (title) {
            const date = new Date(`${point.dataset.chartDate}T00:00:00Z`);
            title.textContent = `${date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', timeZone: 'UTC' })}: ${formatRupiah(amount)}`;
        }

        return `${index === 0 ? 'M' : 'L'} ${x.toFixed(1)} ${y.toFixed(1)}`;
    }).join(' ');

    chart.querySelector('.chart-line')?.setAttribute('d', line);
    chart.querySelector('.chart-area')?.setAttribute('d', `${line} L 980 238 L 84 238 Z`);
};

document.querySelectorAll('[data-delete-transaction]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        const row = form.closest('[data-transaction-row]');
        const feedback = document.querySelector('[data-transaction-feedback]');

        if (!(button instanceof HTMLButtonElement) || !(row instanceof HTMLTableRowElement)) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Menghapus...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            let data;
            try {
                data = await response.json();
            } catch {
                data = {};
            }

            if (!response.ok) {
                throw new Error(typeof data?.message === 'string'
                    ? data.message
                    : 'Catatan gagal dihapus. Silakan coba lagi.');
            }
            if (
                typeof data !== 'object'
                || data === null
                || !Number.isFinite(Number(data.total))
                || !Number.isFinite(Number(data.monthlyTotal))
                || !Number.isInteger(Number(data.daysWithTransactions))
                || typeof data.dailyTotals !== 'object'
                || data.dailyTotals === null
            ) {
                throw new Error('Respons penghapusan tidak valid. Muat ulang halaman dan coba lagi.');
            }

            row.remove();
            const total = document.querySelector('[data-finance-total]');
            const monthlyTotal = document.querySelector('[data-monthly-total]');
            const daysWithTransactions = document.querySelector('[data-days-with-transactions]');

            if (total) total.textContent = formatRupiah(data.total);
            if (monthlyTotal) monthlyTotal.textContent = formatRupiah(data.monthlyTotal);
            if (daysWithTransactions) daysWithTransactions.textContent = String(data.daysWithTransactions);

            updateTransactionChart(data);

            const tbody = form.closest('.table-card')?.querySelector('.expense-table tbody');
            if (tbody && !tbody.querySelector('[data-transaction-row]')) {
                const emptyRow = document.createElement('tr');
                const emptyCell = document.createElement('td');
                emptyCell.colSpan = form.closest('.table-card').querySelectorAll('.expense-table thead th').length;
                emptyCell.className = 'empty-state';
                emptyCell.textContent = 'Belum ada catatan. Tambahkan catatan pertamamu.';
                emptyRow.append(emptyCell);
                tbody.replaceChildren(emptyRow);
            }

            if (feedback) {
                feedback.textContent = data.message;
                feedback.hidden = false;
            }

        } catch (error) {
            button.disabled = false;
            button.textContent = 'Hapus';

            if (feedback) {
                feedback.textContent = error instanceof Error
                    ? error.message
                    : 'Catatan gagal dihapus. Silakan coba lagi.';
                feedback.hidden = false;
            }
        }
    });
});

document.addEventListener('submit', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLFormElement) || !target.matches('[data-admin-action]')) {
        return;
    }

    event.preventDefault();
    const form = target;

    if (!window.confirm(form.dataset.confirm ?? 'Lanjutkan tindakan ini?')) {
        return;
    }

    const button = form.querySelector('button[type="submit"]');
    const row = form.closest('[data-admin-user-row]');
    const feedback = document.querySelector('[data-admin-feedback]');
    const action = form.dataset.adminAction;

    if (!(button instanceof HTMLButtonElement) || !(row instanceof HTMLTableRowElement) || !feedback) {
        return;
    }

    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = action === 'delete' ? 'Menghapus...' : 'Mereset...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        let data;
        try {
            data = await response.json();
        } catch {
            data = null;
        }

        if (!response.ok) {
            throw new Error(typeof data?.message === 'string'
                ? data.message
                : 'Tindakan gagal dilakukan. Silakan coba lagi.');
        }
        if (typeof data?.message !== 'string') {
            throw new Error('Respons server tidak valid. Muat ulang halaman dan coba lagi.');
        }

        if (action === 'delete') {
            row.remove();
            const tbody = document.querySelector('[data-admin-users]');
            if (tbody && !tbody.querySelector('[data-admin-user-row]')) {
                const emptyRow = document.createElement('tr');
                const emptyCell = document.createElement('td');
                emptyCell.colSpan = 4;
                emptyCell.className = 'empty-state';
                emptyCell.dataset.adminEmpty = '';
                emptyCell.textContent = 'Belum ada akun pengguna lain.';
                emptyRow.append(emptyCell);
                tbody.replaceChildren(emptyRow);
            }
        } else {
            if (!Number.isInteger(data.incomesCount) || !Number.isInteger(data.expensesCount)) {
                throw new Error('Respons reset tidak valid. Muat ulang halaman dan coba lagi.');
            }

            const counts = row.querySelector('[data-account-finance-counts]');
            if (counts) {
                counts.textContent = `${data.incomesCount} pemasukan · ${data.expensesCount} pengeluaran`;
            }
        }

        feedback.textContent = data.message;
        feedback.hidden = false;
    } catch (error) {
        button.disabled = false;
        button.textContent = originalText;
        feedback.textContent = error instanceof Error
            ? error.message
            : 'Tindakan gagal dilakukan. Silakan coba lagi.';
        feedback.hidden = false;
    }
});
