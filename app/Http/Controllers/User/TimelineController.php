<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers;
use App\Library\BaseClass;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; 
use \App\Post;
use \App\Comment;
use \App\User;

class TimelineController extends Controller
{
    
   public function index(Request $request)
   {
        //自分の投稿とフォローしてるユーザの投稿を取得してそれを作成日時順で表示
        $user_posts = Auth::user()->posts;
        
        //dd($request);
        $counts = BaseClass::counts(Auth::user());
       // dd($user_posts);
       // もしログインユーザが誰かをフォローしていたならforeachでフォローしてるユーザ１つ１つの投稿を取得
        $following_users = Auth::user()->followings;
        //dd($following_users);
        if($following_users != NULL)
        {
            foreach($following_users as $following_user){
                if(!empty($following_user->posts)){
                    $all_posts = $user_posts->merge($following_user->posts);
                    $all_posts = $all_posts-> sortByDesc('created_at');
                }else{
                    $all_posts = $user_posts-> sortByDesc('created_at');
                }
                
            }
        }
        //dd($all_posts);
        foreach($all_posts as $post){
            $count_liking_users = $post->liking_users->count();
            //dd($count_liking_users);
            $data=[
               'count_liking_users'=>$count_liking_users,
            ];
        }
          //dd($all_posts);
            return view('user.timeline.index',compact(
                'all_posts',
                'counts',
                'data'
            ));
        
   }
   
   public function post(Request $request)
   {
        //dd($request);
        $this->validate($request, Post::$rules);
        //dd($request);
        if($request->hasFile('image')){
            $request->file('image')->store('/public/images');
            Post::create([ 
                'user_id' => Auth::id(), 
                'post' => $request->post, 
                'image' => $request->file('image')->hashName(),
            ]);
         
        }else{
            Post::create([ 
                'user_id' => Auth::id(), 
                'post' => $request->post, 
            ]);
           
        }
        return back(); 
    }
    
    public function show(Request $request)
    {
        //クリックした投稿のID
        $post = Post::find($request->id);
        //１つの投稿を表示する際それについてるコメントを表示
        $comments = Comment::where('post_id',$post)->latest()->get();
      
        return view('user.timeline.detail', compact(
            'post',
            'comments'
        ));
    }
    
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        //dd($keyword);
        if (!empty($keyword)) {
            $searched_users = User::where('name', $keyword)->get();
            $searched_posts = Post::where('post', $keyword)->get();
                
        }
 
        return view('user.timeline.search', compact(
            'keyword', 
            'searched_users', 
            'searched_posts'
        ));
    }
}
