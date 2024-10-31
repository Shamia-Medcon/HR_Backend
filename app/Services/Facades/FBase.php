<?php


namespace App\Services\Facades;

use App\Helper\_MediaHelper;
use App\Services\Interfaces\IBase;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Nette\Schema\ValidationException;

class FBase implements IBase
{

    private $helper;
    protected $model,
        $translatable = false,
        $translatableColumn = [],
        $table,
        $columns,
        $rules,
        $props = [],
        $searchProps = [],
        $search, //search columns
        $slug, //to compare column if it`s already exist or not
        $slugging, //this is the column will use it to extract the slug
        $private, //Use it to check the permission, as we will use it to check whether the user has permission to access this records or not
        $privateInstance, //the table will use it to check the permission
        $privateColumn, //the column will use it to check the permission
        $selectedColumn, //the column will get it after check the permission
        $privateId, //the id for the users
        $trackExist, //check if auth id will store it in table
        $trackId, //the auth id
        $trackColumn, //the auth column in track table
        $encrypt = false, // to check if this table has encrypt column
        $encryptColumn, //name of the encrypt column
        $salt_hash, //name of the salt hash column
        $unique,
        $hasUnique,
        $verificationEmail, //to check if this model can send verification email or not
        $orderBy = "asc",
        $columnOrdering = "created_at",
        $dateColumns;


    public function __instance()
    {
        return new $this->model;
    }

    public function _instancePrivate()
    {
        return new $this->privateInstance;
    }

    public function validation(Request $request)
    {
        try {
            $request->validate($this->rules);
            return true;
        } catch (ValidationException $exception) {
            return $exception;
        }
    }

    public function make($value)
    {
        return hash('sha256', $value);
    }

    public function check($value, $hashedValue, array $options = [])
    {
        // Verify the hash here e.g.
        return $this->make($value) === $hashedValue;
        // But more secure than this
    }

    public function getColumn(Request $request)
    {
        $columns = [];
        if ($this->slug) {
            if (in_array($this->slugging, $this->translatableColumn)) {
                $value = Str::slug($request->input($this->slugging . '.en'));
                $slug = $value;
            } else {
                $slug = Str::slug($request->input($this->slugging));
            }
            $columns = [$this->unique => $slug];
        }
        $all = $request->all();
        foreach ($all as $key => $item) {
            if (($k = array_search($key, $this->columns)) !== false) {
                if (in_array($key, $this->translatableColumn)) {
                    $value = [
                        'en' => $item['en'],
                        'ar' => $item['ar'],
                    ];
                    $columns = array_merge($columns, [$key => $value]);
                } else {
                    $columns = array_merge($columns, [$key => $item]);
                }
            }
        }
        if ($this->encrypt) {
            $salt = Str::random(20);
            $hashed = $this->make($salt . $columns[$this->encryptColumn]);
            $columns[$this->encryptColumn] = $hashed;
            $columns[$this->salt_hash] = $salt;
        }
        if ($this->trackExist) {
            $columns[$this->trackColumn] = $this->trackId;
        }
        return $columns;
    }

    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $wheres = $this->props;
        if ($user && $user->role == 'admin') {
            $wheres = [];
        }
        $temp = $this->__instance()->query();

        $temp = $temp->where($wheres);
        return $temp->orderBy($this->columnOrdering, $this->orderBy)->get();
    }

    public function search(Request $request)
    {

    }

    public function all()
    {
        return $this->__instance()->all();
    }

    public
    function getFeaturedRecord()
    {
        return $this->__instance()->query()->where([
            "is_featured" => true
        ])->first();
    }

    public
    function getLatestRecord()
    {
        return $this->__instance()->query()->where($this->props)->orderBy("created_at", "desc")->get()->first();
    }

    public
    function getTrendingRecord()
    {
        return $this->__instance()->query()->where([
            "is_trending" => true,

        ])->where($this->props)->orderBy("created_at", "desc")->get()->first();
    }

    public
    function media($id)
    {
        $obj = $this->getById($id);
        $this->helper = new _MediaHelper();

        if ($obj) {
            $list = [];
            //Thumbnail English
            $thumbnail = $obj->thumbnail()->where([
                'language' => 'en'
            ])->first();

            if ($thumbnail) {
                $list['thumbnail']['en'] = [
                    'id' => $thumbnail->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $thumbnail->url),
                    'url' => $this->helper->getUrl($thumbnail->url, 'image'),
                ];
            }
            //Thumbnail French
            $thumbnail = $obj->thumbnail()->where([
                'language' => 'ar'
            ])->first();
            if ($thumbnail) {
                $list['thumbnail']['ar'] = [
                    'id' => $thumbnail->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $thumbnail->url),
                    'url' => $this->helper->getUrl($thumbnail->url, 'image'),
                ];
            }
            //Cover French
            $cover = $obj->cover()->where([
                'language' => 'en'
            ])->first();
            if ($cover) {
                $list['cover']['en'] = [
                    'id' => $cover->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $cover->url),
                    'url' => $this->helper->getUrl($cover->url, 'image'),
                ];
            }
            $cover = $obj->cover()->where([
                'language' => 'ar'
            ])->first();
            if ($cover) {
                $list['cover']['ar'] = [
                    'id' => $cover->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $cover->url),
                    'url' => $this->helper->getUrl($cover->url, 'image'),
                ];
            }

            //Promo French
            $video = $obj->videos()->where([
                'language' => 'en'
            ])->first();
            if ($video) {
                $list['video']['en'] = [
                    'id' => $video->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $video->url),
                    'url' => $this->helper->getUrl($thumbnail->url, 'video'),
                ];
            }
            $video = $obj->videos()->where([
                'language' => 'ar'
            ])->first();
            if ($video) {
                $list['video']['ar'] = [
                    'id' => $video->id,
                    //                    'url' => asset('/storage/' . $prefix . '/' . $id . '/' . $video->url),
                    'url' => $this->helper->getUrl($video->url, 'video'),
                ];
            }
            return $list;
        }
        return [];
    }

    public
    function getByDate(Request $request)
    {
        $temp = $this->__instance()->query();
        if ($request->has('q')) {
            $queryList = explode(' ', $request->input('q'));
            foreach ($queryList as $queryItem) {
                foreach ($this->search as $item) {
                    $temp = $temp->where($item, 'like', '%' . $queryItem . '%');
                }
            }
        }
        if ($request->has('queryDate')) {
            $queryDate = $request->input('queryDate');
            $start_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "00:00:00");
            $end_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "23:59:59");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '>=', $start_date)->whereDate($dateColumn, '<=', $end_date);
                }
            }
        }
        if ($request->has('startDate')) {
            $queryDate = $request->input('startDate');
            $start_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "00:00:00");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '>=', $start_date);
                }
            }
        }
        if ($request->has('endDate')) {
            $queryDate = $request->input('endDate');
            $end_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "23:59:59");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '<=', $end_date);
                }
            }
        }
        if ($this->private) {
            $temp = $temp->whereIn('id', $this->_instancePrivate()->query()->where($this->privateColumn, $this->privateId)->select($this->selectedColumn)->pluck('event_id')->toArray());
        }
        return $temp->get();
    }

    public function store(Request $request)
    {
        $ex = $this->validation($request);
        if (($ex instanceof ValidationException)) {
            throw new ValidationException($ex->getMessage(), $ex->getMessages());
        }
        if (!$this->checkDuplicate($request)) {
            return null;
        }
        $columns = $this->getColumn($request);

        $model = $this->__instance()->create($columns);

//        if ($this->verificationEmail) {
//            $email = new _EmailHelper();
//            $email->sendVerification($model);
//        }
        return $model;
    }

    public function Counts()
    {
        return $this->__instance()->get()->count();
    }

    public
    function update(Request $request, $id)
    {
        $ex = $this->validation($request);
        if (($ex instanceof ValidationException)) {
            throw new ValidationException($ex->getMessage(), $ex->getMessages());
        }
        $item = $this->getById($id);
        if ($item) {
            if (!$this->checkDuplicate($request, $id)) {
                return null;
            }
            $columns = $this->getColumn($request);
            $item->update($columns);

        }
        return $item;
    }

    public
    function delete($id)
    {
        return $this->__instance()->query()->where(['id' => $id])->delete();
    }

    public
    function getByColumns($columns)
    {
        return $this->__instance()->query()->where($columns);
    }

    public
    function getById($id)
    {
        return $this->__instance()->query()->where(['id' => $id])->first();
    }

    public
    function getBySlug($slug)
    {
        if (in_array('slug', $this->translatableColumn)) {
            return $this->__instance()->query()
                ->whereJsonContains('slug->' . app()->getLocale(), $slug)
                ->first();
        } else {
            return $this->__instance()->query()->where(['slug' => $slug])->first();
        }
    }

    public
    function checkUnique($value, $key, $id = null)
    {
        $check = $this->__instance()->query()->where(
            [$key => $value]
        );
        if ($id) {

            $check = $check->where('id', '!=', $id);
        }
        return $check->first();
    }

    public
    function checkDuplicate(Request $request, $id = null)
    {
        if ($this->hasUnique) {
            $value = "slug";
            switch ($this->unique) {
                case "slug":
                    if ($this->translatable) {
                        $value = Str::slug($request->input($this->slugging . '.en'));
                    } else {
                        $value = Str::slug($request->input($this->slugging));
                    }
                    break;
                case "email":
                case "sku":
                    $value = $request->input($this->unique);
                    break;
                default:
                    break;
            }
            if ($this->checkUnique($value, $this->unique, $id)) {
                return false;
            }
        }
        return true;
    }

    public function uploadImages($object, $files, $type = "image", $lang = "en")
    {
        $this->helper = new _MediaHelper();

        foreach ($files as $key => $image) {
            $slug = Str::slug($object->title);

            $filename = $slug . "_" . $key . "_" . time();
            try {
                $this->helper->upload($image, $filename);
            } catch (Exception $e) {
                return null;
            }

            $object->images()->create([
                'url' => $filename,
                'thumb_url' => $filename,
                'mime_type' => $image->getMimeType(),
                'language' => $lang,
                'type' => $type,
            ]);
        }
        return true;
    }

    public function uploadFile($object, $files)
    {
        $this->helper = new _MediaHelper();
        foreach ($files as $key => $image) {
            $slug = Str::random(10);
            $filename = $slug . "_" . $key . "_" . time();
            try {
                $this->helper->upload($image, $filename);
            } catch (Exception $e) {
                Log::error($e);
                return null;
            }
            $extension = $image->getClientOriginalExtension();

            $object->files()->create([
                'url' => $filename . "." . $extension,
                'thumb_url' => $filename . "." . $extension,
                'mime_type' => $image->getMimeType(),
                'type' => 'file',
            ]);
        }
        return true;
    }

    public function uploadVideo($object, $files, $type = "video", $lang = "en")
    {
        $this->helper = new _MediaHelper();

        foreach ($files as $key => $file) {
            $slug = Str::slug($object->title);
            $filename = $slug . "_" . $key . "_" . time();
            try {
                $this->helper->uploadVideo($file, $filename);
            } catch (Exception $e) {
                Log::error($e);
                return null;
            }
            $object->allMedia()->create([
                'url' => $filename,
                'mime_type' => $file->getMimeType(),
                'language' => $lang,
                'type' => $type,
                'status' => true
            ]);
        }
        return true;
    }

    public
    function destroyMediaByObjectId($obj, $type)
    {
        $this->helper = new _MediaHelper();
        if ($obj) {
            $media = $obj->allMedia()->where('type', '!=', $type)->get();
            foreach ($media as $item) {
                $type = "image";
                if ($media->type == "video" || $media->type == "promo") {
                    $type = "video";
                }
                $res = $this->helper->delete($item->url, $type);
                $item->delete();
            }
            return true;
        }
        return null;
    }


    public
    function destroyMedia($obj_id, $id)
    {
        $this->helper = new _MediaHelper();
        $obj = $this->getById($obj_id);
        if ($obj) {
            $media = $obj->allMedia()->where([
                'id' => $id
            ])->first();
            if ($media) {
                $type = "image";
                if ($media->type == "video" || $media->type == "promo") {
                    $type = "video";
                }
                $res = $this->helper->delete($media->url, $type);
                $media->delete();
                return true;
            }
        }
        return null;
    }
}
