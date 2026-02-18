<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InboxMessagesExport implements FromView
{
    protected $messages;

    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    public function view(): View
    {
        return view('file-exports.inbox_messages', [
            'messages' => $this->messages
        ]);
    }
}
