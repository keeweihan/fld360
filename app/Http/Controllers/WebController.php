<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class WebController extends Controller
{
    public $categories;

    public function __construct()
    {
        $CategoryController = new CategoryController();
        $this->categories = $CategoryController->categories();
        View::share('categories', $this->categories);

        $tags = DB::table('tags')->orderBy('id', 'desc')->get();
        View::share('tags', $tags);
    }

    public function home()
    {
        /*
         * 组装类目图辑数据
         * */
        foreach ($this->categories as $key => $category) {
            // 获取类目下的图辑
            $albums = DB::table('albums')
                ->leftJoin('images', 'albums.id', '=', 'images.album_id')
                ->select('albums.*', DB::raw("count(images.id) as pic_count"))
                ->groupBy('images.album_id')
                ->where('images.is_deleted', 0)
                ->where('albums.is_deleted', 0)
                ->whereIn('albums.cate_id', $category['cate_ids'])
                ->orderBy('albums.created_at', 'desc')
                ->take(18)
                ->get();

            $this->categories[$key]['albums'] = $albums->toArray();
        }


        return view('default-views.home', ['categories' => $this->categories]);
    }

    function category($cate_id)
    {
        $AlbumsController = new AlbumsController();
        $current_cid = $cate_id;

        if (collect($this->categories)->has($cate_id)) {    // 当前类目为主目录
            $subcate_ids = collect($this->categories[$cate_id]['childs'])->keys();
            $albums = $AlbumsController->byCategory($subcate_ids);

            $subcates = collect($this->categories[$cate_id]['childs']);
            $category = $this->categories[$cate_id];
        } else {    // 子类目
            $albums = $AlbumsController->byCategory([$cate_id]);

            foreach ($this->categories as $cid => $cate) {
                if (collect($cate['childs'])->has($cate_id)) {
                    $subcates = collect($cate['childs']);
                    $category = $cate;
                    break;
                }
            }
        }

        return view('default-views.category', compact('albums', 'category', 'current_cid', 'subcates'));
    }

    function album($album_id, $image_id = 0)
    {
        $AlbumController = new AlbumsController();
        $data = $AlbumController->getAlbumById($album_id, $image_id);

//        dd($data);
        $album = $data['album'];
        $images = $data['images'];
        $image = $image_id ? $images->keyBy('id')[$image_id] : $images[$image_id];
        $tags = $data['tags']->keyBy('id');
        $cate = $data['cate'];
        $sub_cate = $data['sub_cate'];

        // 相似图片
        $similar_albums = DB::table('albums')
            ->where(function ($query) use ($tags) {
                foreach ($tags->pluck('id')->all() as $tag_id) {
                    $query->orWhere('tags', 'like', "%$tag_id%");
                }
            })
            ->take(12)
            ->get();
//        dd($tags, $similar_albums);
        View::share('similar_albums', $similar_albums);

        //推荐图辑
        $recommend_albums = $AlbumController->recommend();
        View::share('recommend_albums', $recommend_albums);

        //今日更新
        $today_albums = $AlbumController->today();
        View::share('today_albums', $today_albums);

        return view('default-views.detail', compact('album', 'images', 'image', 'tags', 'cate', 'sub_cate'));
    }


    function tag($tag_id)
    {
        $AlbumController = new AlbumsController();
        $albums = $AlbumController->byTag($tag_id);

        return view('default-views.tag-result', compact('albums'));
    }
}