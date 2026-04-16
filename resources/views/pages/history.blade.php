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
                    <input type="text" placeholder="Search by ID, grade...">
                </div>

                <div class="filter-box select-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 4.455.377 5.555.82.976.394 1.445 1.325 1.2 2.316l-.198.798a3.75 3.75 0 0 1-2.857 2.746l-1.07.238a1.875 1.875 0 0 0-1.458 1.83v5.25a1.875 1.875 0 0 1-3.75 0v-5.25a1.875 1.875 0 0 0-1.458-1.83l-1.07-.238A3.75 3.75 0 0 1 5.64 7.934l-.198-.798c-.245-.99.224-1.922 1.2-2.316C7.545 3.377 9.245 3 12 3Z" />
                    </svg>
                    <select>
                        <option>All Grades</option>
                        <option>Grade A</option>
                        <option>Grade B</option>
                        <option>Grade C</option>
                    </select>
                </div>

                <div class="filter-box select-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V8.25A2.25 2.25 0 0 1 5.25 6h13.5A2.25 2.25 0 0 1 21 8.25v10.5M3 18.75A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M3 18.75v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    <select>
                        <option>Newest First</option>
                        <option>Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="history-toolbar">
            <p>Showing 6 of 6 scans</p>

            <button type="button" class="export-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4.5 15.75v1.5A2.25 2.25 0 0 0 6.75 19.5h10.5a2.25 2.25 0 0 0 2.25-2.25v-1.5" />
                </svg>
                <span>Export CSV</span>
            </button>
        </div>

        <div class="history-list">
            @foreach ($historyItems as $item)
                <div class="history-item-card">
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

                    <button type="button" class="history-arrow-btn" aria-label="View scan details">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection