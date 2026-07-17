@extends('layouts.app')

@section('title','Dashboard Publik')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-8">

    <h2 class="text-3xl font-bold mb-2">

        Dashboard Neraca Pangan

    </h2>

    <p class="text-slate-500 mb-8">

        Sistem Komoditas Neraca Pangan Kabupaten Kediri

    </p>

    {{-- Statistik akan kita isi nanti --}}

    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

        <div class="bg-white rounded-xl shadow p-6">

            <h3 class="text-sm text-gray-500">
                Total Komoditas
            </h3>

            <div class="text-3xl font-bold mt-2">

                {{ $summary['total_komoditas'] }}

            </div>

        </div>

        <div class="bg-white rounded-xl shadow p-6">

            <h3 class="text-sm text-gray-500">
                Surplus
            </h3>

            <div class="text-3xl font-bold text-green-600 mt-2">

                {{ $summary['surplus'] }}

            </div>

        </div>

        <div class="bg-white rounded-xl shadow p-6">

            <h3 class="text-sm text-gray-500">
                Defisit
            </h3>

            <div class="text-3xl font-bold text-red-600 mt-2">

                {{ $summary['defisit'] }}

            </div>

        </div>

        <div class="bg-white rounded-xl shadow p-6">

            <h3 class="text-sm text-gray-500">
                Periode
            </h3>

            <div class="text-3xl font-bold mt-2">

                {{ date('Y') }}

            </div>

        </div>

    </div>

</div>

@endsection