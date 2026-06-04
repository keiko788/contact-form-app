<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
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

    // 検索条件に一致するお問い合わせをCSV形式でエクスポート
    public function export(ExportContactRequest $request)
    {
        // 検索条件
        $query = Contact::with('category');

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

        $contacts = $query->latest()->get();

        return response()->streamDownload(
            function () use ($contacts) {

                // CSV出力用のストリームを開く
                $handle = fopen('php://output', 'w');

                // Excelで文字化けしないようBOMを付与
                fwrite($handle, "\xEF\xBB\xBF");

                // CSVヘッダーを書き込む
                fputcsv($handle, [
                    'ID',
                    '氏名',
                    '性別',
                    'メールアドレス',
                    '電話番号',
                    '住所',
                    '建物名',
                    'カテゴリー',
                    'お問い合わせ内容',
                    '作成日時',
                ]);

                // お問い合わせデータを1件ずつCSVへ出力
                foreach ($contacts as $contact) {
                    fputcsv($handle, [
                        $contact->id,
                        $contact->first_name.' '.$contact->last_name,
                        $contact->gender_label,
                        $contact->email,
                        $contact->tel,
                        $contact->address,
                        $contact->building,
                        $contact->category->content,
                        $contact->detail,
                        $contact->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($handle);
            },

            // ダウンロードするCSVファイル名
            'contacts.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}
