<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['can:project_object_documents_access'])->group(function () {
    Route::get('project-object-documents', 'ProjectObjectDocuments\ProjectObjectDocumentsController@returnPageCore')->name('project-object-documents');
    Route::get('project-object-documents-indexMobile', 'ProjectObjectDocuments\ProjectObjectDocumentsController@indexMobile')->name('projectObjectDocument.indexMobile');
    Route::get('project-object-document-types', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getTypes')->name('projectObjectDocument.getTypes');
    Route::get('project-object-document-statuses', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getStatuses')->name('projectObjectDocument.getStatuses');
    Route::get('project-object-document-options-by-type-and-status', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getOptionsByTypeAndStatus')->name('projectObjectDocument.getOptionsByTypeAndStatus');
    Route::get('project-object-document-projectObjects', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getProjectObjects')->name('projectObjectDocument.getProjectObjects');
    Route::get('project-object-document-projectObjectDocumentComments', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getProjectObjectDocumentComments')->name('projectObjectDocument.getProjectObjectDocumentComments');
    Route::get('project-object-document-projectObjectDocumentAttachments', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getProjectObjectDocumentAttachments')->name('projectObjectDocument.getProjectObjectDocumentAttachments');
    Route::post('/project-object-document-uploadFile', 'ProjectObjectDocuments\ProjectObjectDocumentsController@uploadFile')->name('projectObjectDocument.uploadFile');
    Route::post('/project-object-document-uploadFiles', 'ProjectObjectDocuments\ProjectObjectDocumentsController@uploadFiles')->name('projectObjectDocument.uploadFiles');
    Route::get('project-object-document-getResponsibles', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getResponsibles')->name('projectObjectDocument.getResponsibles');
    Route::get('project-object-document-getProjectObjectResponsibles', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getProjectObjectResponsibles')->name('projectObjectDocument.getProjectObjectResponsibles');
    Route::post('/project-object-document-cloneDocument/{id}', 'ProjectObjectDocuments\ProjectObjectDocumentsController@cloneDocument')->name('projectObjectDocument.clone');
    Route::post('/project-object-document-downloadXls', 'ProjectObjectDocuments\ProjectObjectDocumentsController@downloadXls')->name('projectObjectDocument.downloadXls');
    Route::post('/project-object-document-downloadAttachments', 'ProjectObjectDocuments\ProjectObjectDocumentsController@downloadAttachments')->name('projectObjectDocument.downloadAttachments');
    Route::get('project-object-document-getProjectObjectDocumentInfoByID', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getProjectObjectDocumentInfoByID')->name('projectObjectDocument.getProjectObjectDocumentInfoByID');
    Route::get('project-object-document-getDataForLookupsAndFilters', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getDataForLookupsAndFilters')->name('projectObjectDocument.getDataForLookupsAndFilters');
    Route::get('project-object-document-getPermissions', 'ProjectObjectDocuments\ProjectObjectDocumentsController@getPermissions')->name('projectObjectDocument.getPermissions');

    Route::post('project-object-document-restore/{id}', 'ProjectObjectDocuments\ProjectObjectDocumentsController@restoreDocument')->name('project-object-document.restoreDocument');
    
    Route::apiResource('project-object-document', 'ProjectObjectDocuments\ProjectObjectDocumentsController');
});