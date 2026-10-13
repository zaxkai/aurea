@extends('errors.minimal')

@section('title', __('Terlalu Banyak Permintaan | Aurea'))
@section('code', '429')
@section('heading', __('Terlalu Banyak Permintaan'))
@section('message', __('Too Many Requests'))
@section('bubble', __('Pelan-pelan ya, tarik nafas dulu...'))
@section('description', __('Kamu mengirim terlalu banyak permintaan dalam waktu singkat. Berikan jeda sejenak sebelum mencoba lagi ya!'))
