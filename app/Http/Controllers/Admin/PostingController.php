<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Posts;
use App\History;
use Carbon\Carbon;
use Storage;

class PostingController extends Controller
{
  public function add()
  {
      return view('admin.posts.create');
  }

  public function create(Request $request)
  {
      // このファンクションを実行することによってデータベースのnewsテーブルにデータが追加される
      $this->validate($request, Posts::$rules);
      $posts = new Posts;
      $form = $request->all();
      if (isset($form['image'])) {
          $path = Storage::disk('s3')->putFile('/',$form['image'],'public');
          $posts->image_path = Storage::disk('s3')->url($path);
      } else {
          $posts->image_path = null;
      }

      unset($form['_token']);

      unset($form['image']);

      $posts->fill($form);
      $posts->save();

      return redirect('admin/post/create');
  }

  public function index(Request $request)
  {
      $cond_title = $request->cond_title;
      if ($cond_title != '') {
          // 検索されたら検索結果を取得する
          $posts = Posts::where('title', $cond_title)->get();
      } else {
          // それ以外はすべてのニュースを取得する
          $posts = Posts::all();
      }
      return view('admin.post.index', ['posts' => $posts, 'cond_title' => $cond_title]);
  }

  public function edit(Request $request)
  {
      // News Modelからデータを取得する
      $posts = Posts::find($request->id);
      if (empty($post)) {
        abort(404);    
      }
      return view('admin.post.edit', ['post_form' => $posts]);
  }


  public function update(Request $request)
  {
      // Validationをかける
      $this->validate($request, Posts::$rules);
      // News Modelからデータを取得する
      $posts = Posts::find($request->id);
      // 送信されてきたフォームデータを格納する
      $post_form = $request->all();
      unset($post_form['_token']);
      unset($post_form['image']);
      unset($post_form['remove']);
      $posts->fill($post_form)->save();

      $history = new History();
      $history->news_id = $posts->id;
      $history->edited_at = Carbon::now();
      $history->save();

      return redirect('admin/post/');

      // 該当するデータを上書きして保存する
      $posts->fill($posts_form)->save();

      return redirect('admin/posts');
  }

  public function delete(Request $request)
  {
      // 該当するNews Modelを取得
      $posts = Posts::find($request->id);
      // 削除する
      $posts->delete();
      return redirect('admin/post/');
  }  

  }


   

