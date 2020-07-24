<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Lavary\Menu\Menu;
use Config;

class GenerateMenus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 取得語系
        $language = ! empty($request->cookie("language")) ? $request->cookie("language") : Config::get("app.fallback_locale");
        // 語系
        App::setLocale($language) ;

        $menu = new Menu() ;
        $menu->make('SideBar', function($menu){
            $menu->add('Dashboard', array('url'=>'dashboard'))->data('icon','fa fa-tachometer');

            //註冊的使用者
            $user = $menu->add(trans('sidebar.user.main'), array('url'=>null))->data('icon','fa fa-user')->data('show',true);
            $user->add(trans('sidebar.user.list'), array('url' => 'user'))->data('show',true);

            //會員
            $member = $menu->add(trans('sidebar.member.main'), array('url'=>null))->data('icon','fa fa-users')->data('show',true);
            $member->add(trans('sidebar.member.list'), array('url' => 'member'))->data('show',true);
            $member->add(trans('sidebar.member.new'), array('url' => 'member/create'))->data('show',true);
            $member->add(trans('sidebar.member.log'), array('url' => 'member/log'))->data('show',true);

            //開放資料
            $opendata = $menu->add(trans('sidebar.opendata.weather.main'), array('url'=>null))->data('icon','fa fa-database')->data('show',true);
            $opendata->add(trans('sidebar.opendata.weather.now'), array('url' => 'opendata/weather/now'))->data('show',true);

            //第三方 API
            $third_party = $menu->add(trans('sidebar.third.main'), array('url'=>null))->data('icon','fa fa-commenting-o')->data('show',true);
            $third_party->add(trans('sidebar.third.line.notify'), array('url' => 'line/index'))->data('show',true);

            //Mail: SMTP 
            $smtp = $menu->add(trans('sidebar.mail.main'), array('url'=>null))->data('icon','fa fa-share')->data('show',true);
            $smtp->add(trans('sidebar.mail.notify'), array('url' => 'mail/index'))->data('show',true);

            //報表系列
            $report = $menu->add(trans('sidebar.report.main'), array('url'=>null))->data('icon','fa fa-file-pdf-o')->data('show',true);
            $report->add(trans('sidebar.report.csv'), array('url' => 'report/csv'))->data('show',true);
            $report->add(trans('sidebar.report.pdf'), array('url' => 'report/pdf'))->data('show',true);
            
        });

        return $next($request);
    }
}
