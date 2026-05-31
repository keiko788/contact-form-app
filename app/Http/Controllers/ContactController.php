<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class ContactController extends Controller
{
    // お問い合わせ入力ページを表示
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact(['categories', 'tags']));
    }

    // お問い合わせ内容確認
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);
        $tags = Tag::whereIn('id', $validated['tag_ids'] ?? [])->get();

        return view('contact.confirm', compact(['validated', 'category', 'tags']));
    }

    // お問い合わせ内容新規作成
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $tags = $validated['tag_ids'] ?? [];

        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        $contact->tags()->attach($tags);

        return redirect()->route('contact.thanks');
    }

    // サンクスページ表示
    public function thanks()
    {
        return view('contact.thanks');
    }
}
