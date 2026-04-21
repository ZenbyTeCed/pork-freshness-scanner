@extends('layouts.app')

@section('content')
<div class="history-page">
    <div class="history-container">
        <div class="history-header">
            <h1>Scan History</h1>
            <p>View and manage all freshness analysis records</p>
        </div>

        @php
            $historyItems = [
                [
                    'id' => 'SCAN-000001',
                    'grade' => 'Grade A',
                    'grade_class' => 'a',
                    'confidence' => '96.5%',
                    'date' => 'Apr 15, 2026, 10:30 AM',
                    'timestamp' => '2026-04-15T10:30:00',
                    'color' => 'Light pink to reddish',
                    'surface' => 'Moist, not slimy',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
                [
                    'id' => 'SCAN-000002',
                    'grade' => 'Grade B',
                    'grade_class' => 'b',
                    'confidence' => '88.3%',
                    'date' => 'Apr 15, 2026, 09:15 AM',
                    'timestamp' => '2026-04-15T09:15:00',
                    'color' => 'Slightly darker pink',
                    'surface' => 'Moist with minor drying',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
                [
                    'id' => 'SCAN-000003',
                    'grade' => 'Grade A',
                    'grade_class' => 'a',
                    'confidence' => '94.2%',
                    'date' => 'Apr 15, 2026, 08:45 AM',
                    'timestamp' => '2026-04-15T08:45:00',
                    'color' => 'Bright pink',
                    'surface' => 'Well-moisturized',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
                [
                    'id' => 'SCAN-000004',
                    'grade' => 'Grade C',
                    'grade_class' => 'c',
                    'confidence' => '91.7%',
                    'date' => 'Apr 14, 2026, 06:20 PM',
                    'timestamp' => '2026-04-14T18:20:00',
                    'color' => 'Pale and uneven',
                    'surface' => 'Slightly sticky',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
                [
                    'id' => 'SCAN-000005',
                    'grade' => 'Grade B',
                    'grade_class' => 'b',
                    'confidence' => '89.1%',
                    'date' => 'Apr 14, 2026, 04:10 PM',
                    'timestamp' => '2026-04-14T16:10:00',
                    'color' => 'Moderate pink',
                    'surface' => 'Moist with light dryness',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
                [
                    'id' => 'SCAN-000006',
                    'grade' => 'Grade A',
                    'grade_class' => 'a',
                    'confidence' => '95.0%',
                    'date' => 'Apr 14, 2026, 01:50 PM',
                    'timestamp' => '2026-04-14T13:50:00',
                    'color' => 'Fresh pink',
                    'surface' => 'Even and moist',
                    'image' => 'https://images.unsplash.com/photo-1603048297172-c92544798d5a?auto=format&fit=crop&w=200&q=80',
                ],
            ];
        @endphp

        <div class="history-filter-card">
            <div class="history-filters">
                <div class="filter-box search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6 6a7.5 7.5 0 0 0 10.65 10.65Z" />
                    </svg>
                    <input type="search" id="historySearch" placeholder="Search by ID, grade, color, surface...">
                </div>

                <div class="filter-box select-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 4.455.377 5.555.82.976.394 1.445 1.325 1.2 2.316l-.198.798a3.75 3.75 0 0 1-2.857 2.746l-1.07.238a1.875 1.875 0 0 0-1.458 1.83v5.25a1.875 1.875 0 0 1-3.75 0v-5.25a1.875 1.875 0 0 0-1.458-1.83l-1.07-.238A3.75 3.75 0 0 1 5.64 7.934l-.198-.798c-.245-.99.224-1.922 1.2-2.316C7.545 3.377 9.245 3 12 3Z" />
                    </svg>
                    <select id="gradeFilter" aria-label="Filter by grade">
                        <option value="all">All Grades</option>
                        <option value="a">Grade A</option>
                        <option value="b">Grade B</option>
                        <option value="c">Grade C</option>
                    </select>
                </div>

                <div class="filter-box select-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V8.25A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25v10.5M3 18.75A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M3 18.75v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 15.75v1.5A2.25 2.25 0 0 0 6.75 19.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-1.5" />
                </svg>
                <span>Export CSV</span>
            </button>
        </div>

        <div class="history-list" id="historyList">
            @foreach ($historyItems as $item)
                <a
                    href="{{ route('result', ['id' => $item['id']]) }}"
                    class="history-item-card"
                    data-id="{{ $item['id'] }}"
                    data-grade="{{ $item['grade'] }}"
                    data-grade-class="{{ $item['grade_class'] }}"
                    data-confidence="{{ $item['confidence'] }}"
                    data-date="{{ $item['date'] }}"
                    data-timestamp="{{ $item['timestamp'] }}"
                    data-color="{{ $item['color'] }}"
                    data-surface="{{ $item['surface'] }}"
                    data-search="{{ strtolower($item['id'].' '.$item['grade'].' '.$item['color'].' '.$item['surface']) }}"
                >
                    <div class="history-item-left">
                        <img src="{{ $item['image'] }}" alt="{{ $item['id'] }}">

                        <div class="history-item-content">
                            <div class="history-item-top">
                                <span class="scan-id">{{ $item['id'] }}</span>
                                <span class="grade-pill {{ $item['grade_class'] }}">{{ $item['grade'] }}</span>
                                <span class="confidence-text">{{ $item['confidence'] }} confidence</span>
                            </div>

                            <p class="history-date">{{ $item['date'] }}</p>

                            <div class="history-meta">
                                <span>Color: {{ $item['color'] }}</span>
                                <span class="dot">•</span>
                                <span>Surface: {{ $item['surface'] }}</span>
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

        <div id="historyEmptyState" class="history-empty-state" hidden>
            <h2>No scans found</h2>
            <p>Adjust your search or filters to see more records.</p>
        </div>
    </div>
</div>

<script>
    const historySearch = document.getElementById('historySearch');
    const gradeFilter = document.getElementById('gradeFilter');
    const sortFilter = document.getElementById('sortFilter');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const historyCount = document.getElementById('historyCount');
    const historyList = document.getElementById('historyList');
    const historyEmptyState = document.getElementById('historyEmptyState');
    const historyCards = Array.from(document.querySelectorAll('.history-item-card'));
    let visibleHistoryCards = [...historyCards];

    const normalize = (value) => value.toString().trim().toLowerCase();

    const updateHistory = () => {
        const searchTerm = normalize(historySearch.value);
        const selectedGrade = gradeFilter.value;
        const sortDirection = sortFilter.value;

        visibleHistoryCards = historyCards.filter((card) => {
            const matchesSearch = !searchTerm || card.dataset.search.includes(searchTerm);
            const matchesGrade = selectedGrade === 'all' || card.dataset.gradeClass === selectedGrade;

            return matchesSearch && matchesGrade;
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

        const headers = ['Scan ID', 'Grade', 'Confidence', 'Date', 'Color', 'Surface'];
        const rows = visibleHistoryCards.map((card) => [
            card.dataset.id,
            card.dataset.grade,
            card.dataset.confidence,
            card.dataset.date,
            card.dataset.color,
            card.dataset.surface,
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
    gradeFilter.addEventListener('change', updateHistory);
    sortFilter.addEventListener('change', updateHistory);
    exportCsvBtn.addEventListener('click', exportVisibleRows);
    updateHistory();
</script>
@endsection
