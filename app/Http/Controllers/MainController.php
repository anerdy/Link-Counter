<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnterDomainRequest;
use App\Services\SiteService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class MainController extends Controller
{

    public function index()
    {
        return view('welcome', []);
    }

    public function getLinks(EnterDomainRequest $request, SiteService $siteService)
    {
        $requestArray = $request->validated();
        $result = $siteService->getLinks($requestArray['domain']);
        if ($result) {
            return redirect('/domain/'.$requestArray['domain']);
        } else {
            return redirect('/?error=1');
        }
    }

    public function getSite(Request $request, SiteService $siteService)
    {
        $links = $siteService->getSite($request->domain_name);
        if (empty($links))
            return redirect('/?error=1');

        return view('domain', compact('links'));
    }

}
