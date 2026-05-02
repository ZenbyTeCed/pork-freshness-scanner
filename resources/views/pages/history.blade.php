@extends('layouts.app')

@section('content')
<div class="history-page">
    <div class="history-container">
        <div class="history-header">
            <h1>Scan History</h1>
            <p>View uploaded image and ESP32-CAM freshness analysis records</p>
        </div>

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

        <div class="history-toolbar">
            <p id="historyCount">Showing {{ count($historyItems) }} of {{ count($historyItems) }} scans</p>

            <button type="button" id="exportCsvBtn" class="export-btn">
                <span>Export CSV</span>
            </button>
        </div>

        <div class="history-list" id="historyList">
            @foreach ($historyItems as $item)
                <a
                    href="{{ route('result', ['historyId' => $item['id']]) }}"
                    class="history-item-card"
                    data-id="{{ $item['id'] }}"
                    data-prediction-class="{{ $item['prediction_class'] }}"
                    data-confidence="{{ $item['confidence_label'] }}"
                    data-date="{{ $item['date_label'] }}"
                    data-timestamp="{{ $item['timestamp'] }}"
                    data-source="{{ $item['source_label'] }}"
                    data-prediction="{{ $item['prediction_label'] }}"
                    data-search="{{ $item['search_text'] }}"
                >
                    <div class="history-item-left">
                        <img src="{{ $item['image_url'] }}" alt="{{ $item['id'] }}">

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
                </a>
            @endforeach
        </div>

        <div id="historyEmptyState" class="history-empty-state" @if (count($historyItems) > 0) hidden @endif>
            <h2>No scans found</h2>
            <p>Analyze an image or run an ESP32-CAM scan to create your first record.</p>
        </div>
    </div>
</div>

<script>
    const historySearch = document.getElementById('historySearch');
    const predictionFilter = document.getElementById('predictionFilter');
    const sortFilter = document.getElementById('sortFilter');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const historyCount = document.getElementById('historyCount');
    const historyList = document.getElementById('historyList');
    const historyEmptyState = document.getElementById('historyEmptyState');
    const historyCards = Array.from(document.querySelectorAll('.history-item-card'));
    let visibleHistoryCards = [...historyCards];

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
            const firstTime = new Date(firstCard.dataset.timestamp).getTime();
            const secondTime = new Date(secondCard.dataset.timestamp).getTime();

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

    historySearch.addEventListener('input', updateHistory);
    predictionFilter.addEventListener('change', updateHistory);
    sortFilter.addEventListener('change', updateHistory);
    exportCsvBtn.addEventListener('click', exportVisibleRows);
    updateHistory();
</script>
@endsection
