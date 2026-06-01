<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Category;
use App\Models\Contact;
use App\Http\Requests\IndexContactRequest;

class AdminController extends Controller
{
    /**
     * 管理者画面（一覧）を取得
     */
    public function index(IndexContactRequest $request)
    {
        $categories = Category::all();
        $tags = Tag::all();

        $query = Contact::with(['tags', 'category']);

        // 検索条件の指定
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        if ($request->filled('gender') && $request->gender != 0) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // ページネーションの指定
        $contacts = $query
            ->latest()
            ->paginate(7);

        return view('admin.index', compact('contacts', 'tags', 'categories'));
    }


    /**
     * お問い合わせ詳細画面を取得
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせの削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
