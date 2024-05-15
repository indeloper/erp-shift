<?php

use App\Http\Controllers\ProjectObjectDocuments;
use App\Http\Controllers\ProjectObjectDocuments\ProjectObjectDocuments;
use Illuminate\Support\Facades\Route;

Route::middleware(['can:project_object_documents_access'])->group(function () {
    Route::get('project-object-documents', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'returnPageCore'])->name('project-object-documents');
    Route::get('project-object-documents-indexMobile', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'indexMobile'])->name('projectObjectDocument.indexMobile');
    Route::get('project-object-document-types', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getTypes'])->name('projectObjectDocument.getTypes');
    Route::get('project-object-document-statuses', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getStatuses'])->name('projectObjectDocument.getStatuses');
    Route::get('project-object-document-options-by-type-and-status', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getOptionsByTypeAndStatus'])->name('projectObjectDocument.getOptionsByTypeAndStatus');
    Route::get('project-object-document-projectObjects', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getProjectObjects'])->name('projectObjectDocument.getProjectObjects');
    Route::get('project-object-document-projectObjectDocumentComments', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getProjectObjectDocumentComments'])->name('projectObjectDocument.getProjectObjectDocumentComments');
    Route::get('project-object-document-projectObjectDocumentAttachments', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getProjectObjectDocumentAttachments'])->name('projectObjectDocument.getProjectObjectDocumentAttachments');
    Route::post('/project-object-document-uploadFile', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'uploadFile'])->name('projectObjectDocument.uploadFile');
    Route::post('/project-object-document-uploadFiles', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'uploadFiles'])->name('projectObjectDocument.uploadFiles');
    Route::get('project-object-document-getResponsibles', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getResponsibles'])->name('projectObjectDocument.getResponsibles');
    Route::get('project-object-document-getProjectObjectResponsibles', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getProjectObjectResponsibles'])->name('projectObjectDocument.getProjectObjectResponsibles');
    Route::post('/project-object-document-cloneDocument/{id}', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'cloneDocument'])->name('projectObjectDocument.clone');
    Route::post('/project-object-document-downloadXls', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'downloadXls'])->name('projectObjectDocument.downloadXls');
    Route::post('/project-object-document-downloadAttachments', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'downloadAttachments'])->name('projectObjectDocument.downloadAttachments');
    Route::get('project-object-document-getProjectObjectDocumentInfoByID', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getProjectObjectDocumentInfoByID'])->name('projectObjectDocument.getProjectObjectDocumentInfoByID');
    Route::get('project-object-document-getDataForLookupsAndFilters', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getDataForLookupsAndFilters'])->name('projectObjectDocument.getDataForLookupsAndFilters');
    Route::get('project-object-document-getPermissions', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'getPermissions'])->name('projectObjectDocument.getPermissions');

    Route::post('project-object-document-restore/{id}', [ProjectObjectDocuments\ProjectObjectDocumentsController::class, 'restoreDocument'])->name('project-object-document.restoreDocument');

    Route::apiResource('project-object-document', ProjectObjectDocuments\ProjectObjectDocumentsController::class);
});
