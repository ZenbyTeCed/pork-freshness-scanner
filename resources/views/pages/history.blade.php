@extends('layouts.app')

@section('content')
{{-- Scan history page --}}
<div class="history-page">
    <div class="history-container">
        <div class="history-header">
            <h1>Scan History</h1>
            <p>View uploaded image and ESP32-CAM freshness analysis records</p>
        </div>

        {{-- Search, filter, and sort controls --}}
        <div class="history-filter-card">
            <div class="history-filters">
                <div class="filter-box search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6 6a7.5 7.5 0 0 0 10.65 10.65Z" />
                    </svg>
                    <input type="search" id="historySearch" placeholder="Search ID, upload, ESP32, fresh, not fresh, date...">
                </div>

                <div class="filter-box select-box">
                    <select id="predictionFilter" aria-label="Filter by prediction">
                        <option value="all">All Results</option>
                        <option value="fresh">Fresh</option>
                        <option value="not_fresh">Not Fresh</option>
                    </select>
                </div>

                <div class="filter-box select-box">
                    <select id="sortFilter" aria-label="Sort scan history">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- History list actions --}}
        <div class="history-toolbar">
            <div class="history-count-group">
                <p id="historyCount">Showing {{ count($historyItems) }} of {{ count($historyItems) }} scans</p>

                <button type="button" id="exportCsvBtn" class="export-btn">
                    <span>Export CSV</span>
                </button>
            </div>

            <div class="history-actions">
                <label class="select-all-control">
                    <input type="checkbox" id="selectAllHistory">
                    <span>Select all</span>
                </label>

                <button type="button" id="deleteSelectedBtn" class="delete-history-btn" disabled>
                    <span>Delete Selected</span>
                </button>
            </div>
        </div>

        {{-- Scan history records --}}
        <div class="history-list" id="historyList">
            @foreach ($historyItems as $item)
                <div
                    class="history-item-card"
                    data-id="{{ $item['id'] }}"
                    data-url="{{ route('result', ['historyId' => $item['id']]) }}"
                    data-prediction-class="{{ $item['prediction_class'] }}"
                    data-confidence="{{ $item['confidence_label'] }}"
                    data-date="{{ $item['date_label'] }}"
                    data-timestamp="{{ $item['timestamp'] }}"
                    data-sort-value="{{ $item['sort_value'] }}"
                    data-source="{{ $item['source_label'] }}"
                    data-prediction="{{ $item['prediction_label'] }}"
                    data-search="{{ $item['search_text'] }}"
                >
                    <label class="history-select" aria-label="Select history {{ $item['id'] }}">
                        <input type="checkbox" class="history-checkbox" value="{{ $item['id'] }}">
                        <span></span>
                    </label>

                    <div class="history-item-left">
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['id'] }}" data-fallback-image="/images/Porky%20Logo.png">

                        <div class="history-item-content">
                            <div class="history-item-top">
                                <span class="scan-id">{{ $item['id'] }}</span>
                                <span class="prediction-pill {{ $item['prediction_class'] }}">{{ $item['prediction_label'] }}</span>
                                <span class="confidence-text">{{ $item['confidence_label'] }} confidence</span>
                            </div>

                            <p class="history-date">{{ $item['date_label'] }}</p>

                            <div class="history-meta">
                                <span>Source: {{ $item['source_label'] }}</span>
                                <span class="dot">&bull;</span>
                                <span>Prediction: {{ $item['prediction_label'] }}</span>
                            </div>
                        </div>
                    </div>

                    <span class="history-arrow-btn" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                        </svg>
                    </span>
                </div>
            @endforeach
        </div>

        <div id="historyEmptyState" class="history-empty-state" @if (count($historyItems) > 0) hidden @endif>
            <h2>No scans found</h2>
            <p>Analyze an image or run an ESP32-CAM scan to create your first record.</p>
        </div>
    </div>
</div>

{{-- Delete history confirmation modal --}}
<div id="deleteHistoryModal" class="delete-history-modal" hidden>
    <div class="delete-history-backdrop" data-delete-cancel></div>

    <div class="delete-history-panel" role="dialog" aria-modal="true" aria-labelledby="deleteHistoryTitle">
        <div class="delete-history-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.917 21H8.083a2.25 2.25 0 0 1-2.243-1.827L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .397c.34-.059.68-.114 1.022-.166m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916A2.25 2.25 0 0 0 13.5 2.25h-3A2.25 2.25 0 0 0 8.25 4.5v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </div>

        <h2 id="deleteHistoryTitle">Delete selected history?</h2>
        <p id="deleteHistoryMessage">This action cannot be undone.</p>

        <div class="delete-history-actions">
            <button type="button" id="cancelDeleteHistoryBtn" class="delete-modal-btn secondary">Cancel</button>
            <button type="button" id="confirmDeleteHistoryBtn" class="delete-modal-btn primary">Delete</button>
        </div>
    </div>
</div>

<script>
    // Handles history search, sorting, export, and deletion.
    const historySearch = document.getElementById('historySearch');
    const predictionFilter = document.getElementById('predictionFilter');
    const sortFilter = document.getElementById('sortFilter');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const selectAllHistory = document.getElementById('selectAllHistory');
    const historyCount = document.getElementById('historyCount');
    const historyList = document.getElementById('historyList');
    const historyEmptyState = document.getElementById('historyEmptyState');
    const deleteHistoryModal = document.getElementById('deleteHistoryModal');
    const deleteHistoryMessage = document.getElementById('deleteHistoryMessage');
    const cancelDeleteHistoryBtn = document.getElementById('cancelDeleteHistoryBtn');
    const confirmDeleteHistoryBtn = document.getElementById('confirmDeleteHistoryBtn');
    const historyCards = Array.from(document.querySelectorAll('.history-item-card'));
    const historyCheckboxes = Array.from(document.querySelectorAll('.history-checkbox'));
    let visibleHistoryCards = [...historyCards];
    let pendingDeleteIds = [];

    const normalize = (value) => value
        .toString()
        .toLowerCase()
        .replace(/[_-]/g, ' ')
        .replace(/[^a-z0-9.%\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();

    const getSearchText = (card) => normalize([
        card.dataset.search,
        card.dataset.id,
        card.dataset.predictionClass,
        card.dataset.confidence,
        card.dataset.date,
        card.dataset.source,
        card.dataset.prediction,
    ].join(' '));

    const searchIndex = new Map(historyCards.map((card) => [card, getSearchText(card)]));

    const updateHistory = () => {
        const searchTerms = normalize(historySearch.value).split(' ').filter(Boolean);
        const selectedPrediction = predictionFilter.value;
        const sortDirection = sortFilter.value;

        visibleHistoryCards = historyCards.filter((card) => {
            const searchableText = searchIndex.get(card) || '';
            const matchesSearch = searchTerms.length === 0 ||
                searchTerms.every((term) => searchableText.includes(term));
            const matchesPrediction = selectedPrediction === 'all' || card.dataset.predictionClass === selectedPrediction;

            return matchesSearch && matchesPrediction;
        });

        visibleHistoryCards.sort((firstCard, secondCard) => {
            const firstTime = Number(firstCard.dataset.sortValue || 0);
            const secondTime = Number(secondCard.dataset.sortValue || 0);

            return sortDirection === 'oldest'
                ? firstTime - secondTime
                : secondTime - firstTime;
        });

        historyCards.forEach((card) => {
            card.classList.add('is-hidden');
        });

        visibleHistoryCards.forEach((card) => {
            card.classList.remove('is-hidden');
            historyList.appendChild(card);
        });

        historyCount.textContent = `Showing ${visibleHistoryCards.length} of ${historyCards.length} scans`;
        historyEmptyState.hidden = visibleHistoryCards.length > 0;
        exportCsvBtn.disabled = visibleHistoryCards.length === 0;
        updateSelectionState();
    };

    const selectedCheckboxes = () => historyCheckboxes.filter((checkbox) => checkbox.checked);

    const updateSelectionState = () => {
        const visibleCheckboxes = visibleHistoryCards
            .map((card) => card.querySelector('.history-checkbox'))
            .filter(Boolean);
        const selectedVisibleCount = visibleCheckboxes.filter((checkbox) => checkbox.checked).length;
        const selectedCount = selectedCheckboxes().length;

        deleteSelectedBtn.disabled = selectedCount === 0;
        deleteSelectedBtn.textContent = selectedCount > 0
            ? `Delete Selected (${selectedCount})`
            : 'Delete Selected';

        if (visibleCheckboxes.length === 0) {
            selectAllHistory.checked = false;
            selectAllHistory.indeterminate = false;
            selectAllHistory.disabled = true;
            return;
        }

        selectAllHistory.disabled = false;
        selectAllHistory.checked = selectedVisibleCount === visibleCheckboxes.length;
        selectAllHistory.indeterminate = selectedVisibleCount > 0 && selectedVisibleCount < visibleCheckboxes.length;
    };

    const escapeCsvValue = (value) => {
        const stringValue = value ?? '';

        if (/[",\r\n]/.test(stringValue)) {
            return `"${stringValue.replaceAll('"', '""')}"`;
        }

        return stringValue;
    };

    const exportVisibleRows = () => {
        if (visibleHistoryCards.length === 0) return;

        const headers = ['History ID', 'Source', 'Prediction', 'Confidence', 'Date'];
        const rows = visibleHistoryCards.map((card) => [
            card.dataset.id,
            card.dataset.source,
            card.dataset.prediction,
            card.dataset.confidence,
            card.dataset.date,
        ]);
        const csvContent = [headers, ...rows]
            .map((row) => row.map(escapeCsvValue).join(','))
            .join('\r\n');
        const blob = new Blob([`\uFEFF${csvContent}`], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const downloadLink = document.createElement('a');
        const dateStamp = new Date().toISOString().slice(0, 10);

        downloadLink.href = url;
        downloadLink.download = `scan-history-${dateStamp}.csv`;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        URL.revokeObjectURL(url);
    };

    const deleteSelectedHistory = async () => {
        const selectedIds = pendingDeleteIds;

        if (selectedIds.length === 0) return;

        deleteSelectedBtn.disabled = true;
        deleteSelectedBtn.textContent = 'Deleting...';
        confirmDeleteHistoryBtn.disabled = true;
        confirmDeleteHistoryBtn.textContent = 'Deleting...';

        try {
            const response = await fetch("{{ route('history.delete') }}", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ history_ids: selectedIds }),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to delete selected history.');
            }

            selectedIds.forEach((id) => {
                const card = historyCards.find((item) => item.dataset.id === id);
                card?.remove();
            });

            window.location.reload();
        } catch (error) {
            alert(error.message || 'Unable to delete selected history.');
            updateSelectionState();
            confirmDeleteHistoryBtn.disabled = false;
            confirmDeleteHistoryBtn.textContent = 'Delete';
        }
    };

    const openDeleteHistoryModal = () => {
        pendingDeleteIds = selectedCheckboxes().map((checkbox) => checkbox.value);

        if (pendingDeleteIds.length === 0) return;

        deleteHistoryMessage.textContent = pendingDeleteIds.length === 1
            ? 'This will permanently delete 1 history record.'
            : `This will permanently delete ${pendingDeleteIds.length} history records.`;
        confirmDeleteHistoryBtn.disabled = false;
        confirmDeleteHistoryBtn.textContent = 'Delete';
        deleteHistoryModal.hidden = false;
        document.body.classList.add('modal-open');
        confirmDeleteHistoryBtn.focus();
    };

    const closeDeleteHistoryModal = () => {
        deleteHistoryModal.hidden = true;
        document.body.classList.remove('modal-open');
        pendingDeleteIds = [];
    };

    historySearch.addEventListener('input', updateHistory);
    predictionFilter.addEventListener('change', updateHistory);
    sortFilter.addEventListener('change', updateHistory);
    exportCsvBtn.addEventListener('click', exportVisibleRows);
    deleteSelectedBtn.addEventListener('click', openDeleteHistoryModal);
    confirmDeleteHistoryBtn.addEventListener('click', deleteSelectedHistory);
    cancelDeleteHistoryBtn.addEventListener('click', closeDeleteHistoryModal);
    deleteHistoryModal.addEventListener('click', (event) => {
        if (event.target?.hasAttribute('data-delete-cancel')) {
            closeDeleteHistoryModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !deleteHistoryModal.hidden) {
            closeDeleteHistoryModal();
        }
    });
    selectAllHistory.addEventListener('change', () => {
        visibleHistoryCards.forEach((card) => {
            const checkbox = card.querySelector('.history-checkbox');

            if (checkbox) {
                checkbox.checked = selectAllHistory.checked;
                card.classList.toggle('is-selected', checkbox.checked);
            }
        });

        updateSelectionState();
    });
    historyCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('click', (event) => {
            event.stopPropagation();
        });
        checkbox.addEventListener('change', () => {
            checkbox.closest('.history-item-card')?.classList.toggle('is-selected', checkbox.checked);
            updateSelectionState();
        });
    });
    historyCards.forEach((card) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('.history-select')) return;
            window.location.href = card.dataset.url;
        });
    });
    updateHistory();
</script>
@endsection
