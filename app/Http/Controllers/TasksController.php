<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Task;    // 追加


class TasksController extends Controller
{

    
    // getでtasks/にアクセスされた場合の「一覧表示処理」
    public function index()
    {   
        if (\Auth::check()) { // 認証済みの場合
        
         // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザの投稿も取得するように変更しますが、現時点ではこのユーザの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
        
        $data = [
                'tasks' => $tasks,
            ];
        
        // indexビューでそれらを表示
        return view('tasks.index', [
            'tasks' => $tasks,
        ]);
        }
        // ログインしていないときの処理
        return view('auth.login');
    }
    
    // getでtasks/createにアクセスされた場合の「新規登録画面表示処理」
    public function create()
    {   
        if (\Auth::check()) { // 認証済みの場合
        
        $task = new task;

        // タスク作成ビューを表示
        return view('tasks.create', [
            'task' => $task,
        ]);
        } else{
        return view('auth.login');
        }
    }

    // postでtasks/にアクセスされた場合の「新規登録処理」
         public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ]);
        
        // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            
        'status' => $request->status,   // 追加
        'content' => $request->content,
        ]);

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // getでtasks/（任意のid）にアクセスされた場合の「取得表示処理」
    public function show($id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        // タスク詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    // getでtasks/（任意のid）/editにアクセスされた場合の「更新画面表示処理」
    public function edit($id)
    {
         // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);

        // タスク編集ビューでそれを表示
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    // putまたはpatchでtasks/（任意のid）にアクセスされた場合の「更新処理」
    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ]);
        
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        // タスクを更新
        $task->status = $request->status;    // 追加
        $task->content = $request->content;
        $task->save();

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // deleteでtasks/（任意のid）にアクセスされた場合の「削除処理」
    public function destroy($id)
    {
        // idの値でタスクを検索して取得
        $task = \App\Task::findOrFail($id);
        
     // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は、投稿を削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
        }
        // トップページへリダイレクトさせる
        return redirect('/');
    }
}