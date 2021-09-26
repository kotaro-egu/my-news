<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Profile;
use App\ProfileHistory;
use Carbon\Carbon;

class ProfileController extends Controller
{

   public function add()
   {
       return view('admin.profile.create');
   }

   public function create(Request $request)
   {
       // このファンクションを実行することによってデータベースのprofilesテーブルにデータが追加される
      $this->validate($request, Profile::$rules);
      $profile = new Profile;
      $form = $request->all();
      if (isset($form['image'])) {
          $path = $request->file('image')->store('public/image');
          $profile->image_path = basename($path);
      } else {
          $profile->image_path = null;
      }
      
      unset($form['_token']);

      unset($form['image']);

      $profile->fill($form);
      $profile->save();

       return redirect('admin/profile/create');
   }
  
   public function index(Request $request)
   {
       $cond_title = $request->cond_title;
       if ($cond_title != '') {
           $posts = profile::where('title', $cond_title)->get();
       } else {
           $posts = profile::all();
       }
       return view('admin.profile.index', ['posts' => $posts, 'cond_title' => $cond_title]);
   }

   public function edit(Request $request)
   {
       // Profile Modelからデータを取得する
       $profile = profile::find($request->id);
       if (empty($profile)) {
         abort(404);    
       }
       return view('admin.profile.edit', ['profile_form' => $profile]);
   }

   public function update(Request $request)
   {
       // Validationをかける
       $this->validate($request, profile::$rules);
       // News Modelからデータを取得する
       $profile = profile::find($request->id);
       // 送信されてきたフォームデータを格納する
       $profile_form = $request->all();
       unset($profile_form['_token']);
       unset($profile_form['image']);
       unset($profile_form['remove']);
       $profile->fill($profile_form)->save();

       $history = new ProfileHistory();
       $history->profile_id = $profile->id;
       $history->edited_at = Carbon::now();
       $history->save();

       // 該当するデータを上書きして保存する
       $profile->fill($profile_form)->save();
 
       //return redirect('admin/profile');
       return redirect('admin/profile/edit?id='.$profile->id);
   }
   
   public function delete(Request $request)
     {
       // 該当するNews Modelを取得
       $profile = profile::find($request->id);
       // 削除する
       $profile->delete();
       return redirect('admin/profile/');
     }  

}
