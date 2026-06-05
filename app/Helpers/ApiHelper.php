<?php

if (! function_exists('formatAPIResourcePaginate')) {
    function formatAPIResourcePaginate($data)
    {
        // $data adalah array hasil dari response()->getData(true) dari resource collection
        // Struktur asli paginator Laravel: data, links, meta
        // Marvel mungkin mengubah keys, tapi kita akan return seperti biasa
        // Jika ingin persis seperti Marvel, bisa dimodifikasi
        return $data;
    }

    // format custom jika diperlukan
    // function formatAPIResourcePaginate($paginatedData)
    // {
    //     return [
    //         'data' => $paginatedData['data'],
    //         'links' => $paginatedData['links'],
    //         'meta' => $paginatedData['meta'],
    //     ];
    // }
}
