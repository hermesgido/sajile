<?php

use Illuminate\Support\Facades\Route;


Route::namespace('Auth')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/', 'showLoginForm')->name('login');
        Route::post('/', 'login')->name('login');
        Route::get('logout', 'logout')->name('logout');
    });

    // Admin Password Reset
    Route::controller('ForgotPasswordController')->group(function(){
        Route::get('password/reset', 'showLinkRequestForm')->name('password.reset');
        Route::post('password/reset', 'sendResetCodeEmail');
        Route::get('password/code-verify', 'codeVerify')->name('password.code.verify');
        Route::post('password/verify-code', 'verifyCode')->name('password.verify.code');
    });

    Route::controller('ResetPasswordController')->group(function(){
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset.form');
        Route::post('password/reset/change', 'reset')->name('password.change');
    });
});

Route::middleware('admin')->group(function () {
    Route::controller('AdminController')->group(function(){
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('profile', 'profile')->name('profile');
        Route::post('profile', 'profileUpdate')->name('profile.update');
        Route::post('password', 'passwordUpdate')->name('password.update');

        //Notification
        Route::get('notifications','notifications')->name('notifications');
        Route::get('notification/read/{id}','notificationRead')->name('notification.read');
        Route::get('notifications/read-all','readAll')->name('notifications.readAll');

        //Report Bugs
        Route::get('request/report','requestReport')->name('request.report');
        Route::post('request/report','reportSubmit');

        Route::get('download/attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
    });

    // Category Manager
    Route::controller('CategoryController')->name('category.')->prefix('manage/category')->group(function(){
        Route::get('/', 'categories')->name('all');
        Route::post('create', 'categoryStore')->name('store');
        Route::post('update/{category}', 'categoryUpdate')->name('update');
    });

    // Price Plan Manager
    Route::controller('PricePlanController')->name('price.plan.')->prefix('manage/price-plan')->group(function(){
        Route::get('/', 'index')->name('all');
        Route::post('create', 'store')->name('store');
        Route::get('edit/{plan}', 'edit')->name("edit");
        Route::post('update/{plan}', 'update')->name("update");

    });

    // Post Manager
    Route::controller('PostController')->name('posts.')->prefix('manage/posts/')->group(function(){
        Route::get('/', 'posts')->name('list');
        Route::get('delete/{id}', 'deletePost')->name('delete');
        Route::post('create', 'postStore')->name('store');
        Route::post('update/{post}', 'postUpdate')->name('update');
        Route::get('active', 'active')->name('active');
        Route::get('pending', 'pending')->name('pending');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('details/{post}', 'details')->name('details');
        Route::get('/{post}', 'details')->name('details');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');

    });
    Route::controller('PostController')->name('jobs.posts.')->prefix('manage/jobs/')->group(function(){
        Route::get('list', 'job_post_list')->name('list');
    });

    Route::controller('PostController')->name('events.posts.')->prefix('manage/events/')->group(function(){
        Route::get('list', 'events_post_list')->name('list');
    });

    // Post & Comment Report
    Route::controller('PostCommentController')->name('post.comment.')->group(function(){
        Route::get('flags', 'postCommentReports')->name('reports');
        Route::post('status', 'postCommentReportsStatus')->name('status');
    });

 
    // Users Manager
    Route::controller('ManageUsersController')->name('users.')->prefix('manage/users')->group(function(){
        Route::get('/', 'allUsers')->name('all');
        Route::get('active', 'activeUsers')->name('active');
        Route::get('banned', 'bannedUsers')->name('banned');
        Route::get('email/verified', 'emailVerifiedUsers')->name('email.verified');
        Route::get('email/unverified', 'emailUnverifiedUsers')->name('email.unverified');
        Route::get('mobile/unverified', 'mobileUnverifiedUsers')->name('mobile.unverified');
        Route::get('mobile/verified', 'mobileVerifiedUsers')->name('mobile.verified');
        Route::get('mobile/verified', 'mobileVerifiedUsers')->name('mobile.verified');
        
        Route::get('detail/{id}', 'detail')->name('detail');
        Route::post('update/{id}', 'update')->name('update');
        Route::get('send/notification/{id}', 'showNotificationSingleForm')->name('notification.single');
        Route::post('send/notification/{id}', 'sendNotificationSingle')->name('notification.single');
        Route::get('login/{id}', 'login')->name('login');
        Route::post('status/{id}', 'status')->name('status');

        Route::get('send/notification', 'showNotificationAllForm')->name('notification.all');
        Route::post('send/notification', 'sendNotificationAll')->name('notification.all.send');
        Route::get('notification/log/{id}', 'notificationLog')->name('notification.log');
    });

   


    // Deposit Gateway
    Route::name('gateway.')->prefix('payment/gateways')->group(function(){
        // Automatic Gateway
        Route::controller('AutomaticGatewayController')->group(function(){
            Route::get('automatic', 'index')->name('automatic.index');
            Route::get('automatic/edit/{alias}', 'edit')->name('automatic.edit');
            Route::post('automatic/update/{code}', 'update')->name('automatic.update');
            Route::post('automatic/remove/{id}', 'remove')->name('automatic.remove');
            Route::post('automatic/activate/{code}', 'activate')->name('automatic.activate');
            Route::post('automatic/deactivate/{code}', 'deactivate')->name('automatic.deactivate');
        });


        // Manual Methods
        Route::controller('ManualGatewayController')->group(function(){
            Route::get('manual', 'index')->name('manual.index');
            Route::get('manual/new', 'create')->name('manual.create');
            Route::post('manual/new', 'store')->name('manual.store');
            Route::get('manual/edit/{alias}', 'edit')->name('manual.edit');
            Route::post('manual/update/{id}', 'update')->name('manual.update');
            Route::post('manual/activate/{code}', 'activate')->name('manual.activate');
            Route::post('manual/deactivate/{code}', 'deactivate')->name('manual.deactivate');
        });
    });


    // DEPOSIT SYSTEM
    Route::name('deposit.')->controller('DepositController')->prefix('manage/deposits')->group(function(){
        Route::get('/', 'deposit')->name('list');
        Route::get('pending', 'pending')->name('pending');
        Route::get('rejected', 'rejected')->name('rejected');
        Route::get('approved', 'approved')->name('approved');
        Route::get('successful', 'successful')->name('successful');
        Route::get('initiated', 'initiated')->name('initiated');
        Route::get('details/{id}', 'details')->name('details');
        Route::post('reject', 'reject')->name('reject');
        Route::post('approve/{id}', 'approve')->name('approve');

    });


    // WITHDRAW SYSTEM
    Route::name('withdraw.')->prefix('manage/withdrawals')->group(function(){
        Route::controller('WithdrawalController')->group(function(){
            Route::get('pending', 'pending')->name('pending');
            Route::get('approved', 'approved')->name('approved');
            Route::get('rejected', 'rejected')->name('rejected');
            Route::get('log', 'log')->name('log');
            Route::get('details/{id}', 'details')->name('details');
            Route::post('approve', 'approve')->name('approve');
            Route::post('reject', 'reject')->name('reject');
        });


        // Withdraw Method
        Route::controller('WithdrawMethodController')->group(function(){
            Route::get('method/', 'methods')->name('method.index');
            Route::get('method/create', 'create')->name('method.create');
            Route::post('method/create', 'store')->name('method.store');
            Route::get('method/edit/{id}', 'edit')->name('method.edit');
            Route::post('method/edit/{id}', 'update')->name('method.update');
            Route::post('method/activate/{id}', 'activate')->name('method.activate');
            Route::post('method/deactivate/{id}', 'deactivate')->name('method.deactivate');
        });
    });

    // Report
    Route::controller('ReportController')->group(function(){
        Route::get('report/transaction', 'transaction')->name('report.transaction');
        Route::get('report/login/history', 'loginHistory')->name('report.login.history');
        Route::get('report/login/ipHistory/{ip}', 'loginIpHistory')->name('report.login.ipHistory');
        Route::get('report/notification/history', 'notificationHistory')->name('report.notification.history');
        Route::get('report/email/detail/{id}', 'emailDetails')->name('report.email.details');
    });


    // Admin Support
    Route::controller('SupportTicketController')->prefix('support')->group(function(){
        Route::get('tickets', 'tickets')->name('ticket');
        Route::get('tickets/pending', 'pendingTicket')->name('ticket.pending');
        Route::get('tickets/closed', 'closedTicket')->name('ticket.closed');
        Route::get('tickets/answered', 'answeredTicket')->name('ticket.answered');
        Route::get('tickets/view/{id}', 'ticketReply')->name('ticket.view');
        Route::post('ticket/reply/{id}', 'replyTicket')->name('ticket.reply');
        Route::post('ticket/close/{id}', 'closeTicket')->name('ticket.close');
        Route::get('ticket/download/{ticket}', 'ticketDownload')->name('ticket.download');
        Route::post('ticket/delete/{id}', 'ticketDelete')->name('ticket.delete');
    });


    // Language Manager
    Route::controller('LanguageController')->prefix('manage')->group(function(){
        Route::get('languages', 'langManage')->name('language.manage');
        Route::post('language', 'langStore')->name('language.manage.store');
        Route::post('language/delete/{id}', 'langDelete')->name('language.manage.delete');
        Route::post('language/update/{id}', 'langUpdate')->name('language.manage.update');
        Route::get('language/edit/{id}', 'langEdit')->name('language.key');
        Route::post('language/import', 'langImport')->name('language.import.lang');
        Route::post('language/store/key/{id}', 'storeLanguageJson')->name('language.store.key');
        Route::post('language/delete/key/{id}', 'deleteLanguageJson')->name('language.delete.key');
        Route::post('language/update/key/{id}', 'updateLanguageJson')->name('language.update.key');
    });

    Route::controller('GeneralSettingController')->group(function(){
        // General Setting
        Route::get('global/settings', 'index')->name('setting.index');
        Route::post('global/settings', 'update')->name('setting.update');

        //configuration
        Route::post('setting/system-configuration','systemConfigurationSubmit');

        // Logo-Icon
        Route::get('setting/logo', 'logoIcon')->name('setting.logo.icon');
        Route::post('setting/logo', 'logoIconUpdate')->name('setting.logo.icon');

        //Cookie
        Route::get('cookie','cookie')->name('setting.cookie');
        Route::post('cookie','cookieSubmit');

        //socialite credentials
        Route::get('setting/social/credentials', 'socialiteCredentials')->name('setting.socialite.credentials');
        Route::post('setting/social/credentials/update/{key}', 'updateSocialiteCredential')->name('setting.socialite.credentials.update');
        Route::post('setting/social/credentials/status/{key}', 'updateSocialiteCredentialStatus')->name('setting.socialite.credentials.status.update');

    });

    //Notification Setting
    Route::name('setting.notification.')->controller('NotificationController')->prefix('notifications')->group(function(){
        //Template Setting
        Route::get('global','global')->name('global');
        Route::post('global/update','globalUpdate')->name('global.update');
        Route::get('templates','templates')->name('templates');
        Route::get('template/edit/{id}','templateEdit')->name('template.edit');
        Route::post('template/update/{id}','templateUpdate')->name('template.update');

        //Email Setting
        Route::get('email/setting','emailSetting')->name('email');
        Route::post('email/setting','emailSettingUpdate');
        Route::post('email/test','emailTest')->name('email.test');

        //SMS Setting
        Route::get('sms/setting','smsSetting')->name('sms');
        Route::post('sms/setting','smsSettingUpdate');
        Route::post('sms/test','smsTest')->name('sms.test');
    });

    // Plugin
    Route::controller('ExtensionController')->group(function(){
        Route::get('extensions', 'index')->name('extensions.index');
        Route::post('extensions/update/{id}', 'update')->name('extensions.update');
        Route::post('extensions/status/{id}', 'status')->name('extensions.status');
    });

    // SEO
    Route::get('seo', 'FrontendController@seoEdit')->name('seo');


    // Frontend
    Route::name('frontend.')->prefix('frontend')->group(function () {

        Route::controller('FrontendController')->group(function(){
            Route::get('templates', 'templates')->name('templates');
            Route::post('templates', 'templatesActive')->name('templates.active');
            Route::get('frontend-sections/{key}', 'frontendSections')->name('sections');
            Route::post('frontend-content/{key}', 'frontendContent')->name('sections.content');
            Route::get('frontend-element/{key}/{id?}', 'frontendElement')->name('sections.element');
            Route::post('remove/{id}', 'remove')->name('remove');
        });

        // Page Builder
        Route::controller('PageBuilderController')->prefix('manage')->group(function(){
            Route::get('pages', 'managePages')->name('manage.pages');
            Route::post('pages', 'managePagesSave')->name('manage.pages.save');
            Route::post('pages/update', 'managePagesUpdate')->name('manage.pages.update');
            Route::post('pages/delete/{id}', 'managePagesDelete')->name('manage.pages.delete');
            Route::get('section/{id}', 'manageSection')->name('manage.section');
            Route::post('section/{id}', 'manageSectionUpdate')->name('manage.section.update');
        });

    });
});

