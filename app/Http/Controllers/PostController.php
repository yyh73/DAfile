<?php

namespace App\Http\Controllers;

//use宣言は外部にあるクラスをインポートできる
//今はApp/Models内のPostクラスにインポートしている。

use App\Models\Post;
use App\Models\Category;
use App\Http\Requests\PostRequest; //useする

class PostController extends Controller
{
    public function index(Post $post)//インポートしたPostをインスタンス化して$postとして使用。
    {
        return view('posts.index')->with(['posts' => $post->getPaginateByLimit()]);//getPaginateByLimit()はpost.phpで定義したメソッドです。
        //blade内で使う変数'posts'と設定。'posts'の中身にgetを使い、インスタンス化した$postを代入。
    }   
    
    //詳細の表示
    public function show(Post $post)
    {
        return view('posts.show')->with(['post' => $post]);
        //'post'はbladeファイルで使う変数。中身は$postはid=1のPostインスタンス。
    }

    public function create(Category $category)
   {
    return view('posts.create')->with(['categories' => $category->get()]);
   }
    
    //投稿の保存
    public function store(Post $post, PostRequest $request)
    {
        //ファームから送られてきたデータ
        $input = $request['post'];

        //Postモデルを使ってフォームを保存
        $post->fill($input)->save();

        //保存後、投稿詳細ページにリダイレクト
        return redirect('/posts/' . $post->id);
    }

    public function edit(Post $post)
    {
    return view('posts.edit')->with(['post' => $post]);
    }

    public function update(PostRequest $request, Post $post)
    {
    $input_post = $request['post'];
    $post->fill($input_post)->save();

    return redirect('/posts/' . $post->id);
    }
    
    public function delete(Post $post)
    {
        $post->delete(); 
        return redirect('/');
    }
}

