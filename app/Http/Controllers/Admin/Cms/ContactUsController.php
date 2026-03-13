<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactPageModel; 
use App\Models\FollowPageModel;   
use App\Models\BannerPageModel; 

class ContactUsController extends Controller
{
    public function index(Request $request)
    {
        // Get the current section from the request or default to 'contact_us'
        $currentSection = $request->get('section', 'contact_us');
        
        // Get data based on the current section
        switch ($currentSection) {
            case 'contact_us':
                $data = ContactPageModel::all(); // Fetch data from the ContactPageModel
                break;

            case 'follow_us':
                $data = FollowPageModel::all(); // Fetch data from the FollowPageModel
                break;

            case 'banner':
                $data = BannerPageModel::all(); // Fetch data from the BannerPageModel
                break;

            default:
                $data = ContactPageModel::all(); // Default data
                break;
        }

        // Return the corresponding view with the data and section
        return view('admin-views.content-management.contact-us.index', [
            'data' => $data,
            'currentSection' => $currentSection,
        ]);
    }
}
