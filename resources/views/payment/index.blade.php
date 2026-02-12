@extends('layouts.app')
@section('content')
    <form action="{{ route('payment.store') }}" method="post">
        @csrf
        <div class="form-floating mb-3">
            <input type="number" class="form-control" id="amount" name="amount" placeholder="To‘lov miqdori">
            <label for="amount">To‘lov miqdori</label>
        </div>
        <div class="mb-3">
            <input type="radio" class="btn-check" name="operator" id="click_btn"
                   value="click" autocomplete="off" checked>
            <label class="btn" for="click_btn">Click</label>

            <input type="radio" class="btn-check" name="operator" id="payme_btn"
                   value="payme" autocomplete="off">
            <label class="btn" for="payme_btn">Payme</label>
        </div>

        <button class="btn btn-primary" type="submit">To‘lovga o‘tish</button>
@endsection
