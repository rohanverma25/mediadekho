<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageMetaResource;
use App\Models\PageMeta;

class PageMetaController extends Controller
{
    /**
     * {page_key} is a fixed value seeded by the create_page_metas_table
     * migration — an unrecognized key 404s via route model binding, same as
     * any other slug-bound resource.
     */
    public function show(PageMeta $pageMeta): PageMetaResource
    {
        return new PageMetaResource($pageMeta);
    }
}
