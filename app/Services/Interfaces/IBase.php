<?php


namespace App\Services\Interfaces;


use Illuminate\Http\Request;

interface IBase
{
    public function index(Request $request);

    public function search(Request $request);

    public function all();
    public function Counts();

    //List of Media for this modal
    public function media($id);

    public function store(Request $request);

    public function update(Request $request, $id);

    public function getById($id);


    public function getLatestRecord();

    public function getTrendingRecord();

    public function getFeaturedRecord();

    public function getBySlug($slug);

    public function getByColumns($columns);

    public function getByDate(Request $request);

    public function uploadImages($object, $files, $type, $lang = "en");
    public function uploadFile($object, $files);

    public function uploadVideo($object, $files, $type, $lang = "en");

    public function delete($id);

    public function destroyMedia($obj_id, $id);

    public function destroyMediaByObjectId($obj, $type);

}
