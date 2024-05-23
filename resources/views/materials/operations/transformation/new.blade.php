@extends('layouts.app')

@section('title', 'Новое преобразование')

@section('url', "#")

@section('css_top')
    <style>
        .dx-form-group {
            background-color: #fff;
            border: 1px solid #cfcfcf;
            border-radius: 1px;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .dx-layout-manager .dx-field-item:not(.dx-first-col) {
            padding-left: 0 !important;
        }

        .transformation-type-selector {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            flex-direction: row;
            align-content: space-between;
        }

        .transformation-type-item {
            width: 120px;
            height: 120px;
            margin: 32px;
            background-color: rgba(183, 183, 183, 0.1);
            border-width: 2px;
            border-style: solid;
            border-color: rgba(183, 183, 183, 0.7);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .transformation-type-text {
            font-weight: 500;
            opacity: 0.8;
            text-align: center;
        }

        .transformation-type-item:hover {
            background-color: aliceblue;
            border-color: #03a9f4a6;
            cursor: pointer;
        }

        .without-box-shadow {
            box-shadow: none !important;
        }

        .form-container .dx-numberbox {
            float: right;
        }

        .transformation-header {
            border-bottom: #e0e0e0 solid 2px !important;
            background-color: #f7f7f7;
        }

        .transformation-header-caption {
            float: left;
            line-height: 28px;
            font-weight: bold;
            color: #717171;
        }

        .transformation-header-button {
            float: right;
            margin-left: 8px;
        }
        .command-row-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dx-datagrid-rowsview .dx-texteditor.dx-editor-outlined .dx-texteditor-input {
            text-align: right;
            padding-right: 0;
        }

        .dx-datagrid-rowsview .dx-placeholder {
            text-align: right;
            width: 100%;
        }

        .dx-datagrid-rowsview .dx-texteditor.dx-editor-outlined .dx-placeholder::before {
            padding-right: 0;
        }

        .computed-weight, .quantity-total-summary, .amount-total-summary, .weight-total-summary {
            text-align: right;
        }

        div.footer-row-validation {
            display: flex;
            align-items: center;
        }

        .footer-row-validation-indicator {
            float: left;
            margin-right: 8px;
        }

        .footer-row-validation-message {
            font-weight: 500;
        }
    </style>
@endsection

@section('content')
    <input type="hidden" name="projectObjectId" id="projectObjectId" value="{{ $projectObjectId }}">
    <div id="formContainer"></div>

    <div id="popupContainer">
        <div id="materialsStandardsAddingForm"></div>
    </div>

    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }"></div>
    </div>

    <div id="validationPopoverContainer">
        <div id="validationTemplate" data-options="dxTemplate: { name: 'validationTemplate' }"></div>
    </div>
@endsection

@section('js_footer')
    {{-- TODO выносим в отдельный файл --}}
    <script src="{{ asset('js/materialsStandardsHelper.js')}}"></script>
@endsection
