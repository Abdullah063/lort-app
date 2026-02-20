    <?php

    use App\Http\Controllers\Api\CompanyController;
    use App\Http\Controllers\Api\GoalController;
    use App\Http\Controllers\Api\InterestController;
    use App\Http\Controllers\Api\GalleryController;
    use App\Http\Controllers\Api\ListingController;
    use App\Http\Controllers\Api\DiscoverController;
    use App\Http\Controllers\Api\MessageController;
    use App\Http\Controllers\Api\NotificationController;
    use App\Http\Controllers\Api\PackageController as AdminPackageController;
    use App\Http\Controllers\Api\PackageLimitController as AdminPackageLimitController;
    use App\Http\Controllers\Api\UserController as AdminUserController;
    use App\Http\Controllers\Api\MembershipController as AdminMembershipController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Auth\AuthController;
    use App\Http\Controllers\Api\ProfileController;

    // =============================================
    // HERKESE AÇIK
    // =============================================
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/social', [AuthController::class, 'socialLogin']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/init', [AuthController::class, 'init']);
        
        
    });

    // =============================================
    // GİRİŞ YAPMIŞ KULLANICI
    // =============================================
    Route::middleware('auth:api')
        ->group(function () {

            // Auth
            Route::prefix('auth')->group(function () {
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::post('/refresh', [AuthController::class, 'refresh']);
                Route::get('/me', [AuthController::class, 'me']);
                Route::post('/send-verification', [AuthController::class, 'sendVerification']);
                Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
                Route::post('/send-sms-verification', [AuthController::class, 'sendSmsVerification']);
                Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
                Route::post('/set-password', [AuthController::class, 'setPassword']);
            });

            // Profil
            Route::prefix('profile')->group(function () {
                Route::get('/status', [ProfileController::class, 'status']);
                Route::post('/user-update', [ProfileController::class, 'updateUser']);
                Route::post('/category', [ProfileController::class, 'setCategory']);
                Route::post('/', [ProfileController::class, 'store']);
                Route::get('/', [ProfileController::class, 'show']);
                Route::put('/', [ProfileController::class, 'update']);
            });

            // Hedefler
            Route::prefix('goals')->group(function () {
                Route::get('/', [GoalController::class, 'index']);
                Route::post('/select', [GoalController::class, 'select']);
                Route::get('/my', [GoalController::class, 'my']);
            });

            // İlgi Alanları
            Route::prefix('interests')->group(function () {
                Route::get('/', [InterestController::class, 'index']);
                Route::post('/select', [InterestController::class, 'select']);
                Route::get('/my', [InterestController::class, 'my']);
            });

            // Şirket
            Route::prefix('company')->group(function () {
                Route::post('/', [CompanyController::class, 'store']);
                Route::get('/', [CompanyController::class, 'show']);
                Route::put('/', [CompanyController::class, 'update']);
            });

            // Fotoğraf Galerisi
            Route::prefix('gallery')->group(function () {
                Route::get('/', [GalleryController::class, 'index']);
                Route::post('/', [GalleryController::class, 'store']);
                Route::post('/reorder', [GalleryController::class, 'reorder']);
                Route::delete('/{id}', [GalleryController::class, 'destroy']);
            });

            // İlanlar
            Route::prefix('listings')->group(function () {
                Route::get('/', [ListingController::class, 'index']);
                Route::post('/', [ListingController::class, 'store']);
                Route::get('/{id}', [ListingController::class, 'show']);
                Route::put('/{id}', [ListingController::class, 'update']);
                Route::delete('/{id}', [ListingController::class, 'destroy']);
            });

            // Keşfet
            Route::prefix('discover')->group(function () {
                Route::get('/', [DiscoverController::class, 'index']);
                Route::post('/swipe', [DiscoverController::class, 'swipe']);
                Route::get('/liked-me', [DiscoverController::class, 'likedMe']);
                Route::get('/my-likes', [DiscoverController::class, 'myLikes']);
            });

            // Mesajlaşma
            Route::get('/conversations', [MessageController::class, 'conversations']);
            Route::post('/conversations/direct', [MessageController::class, 'startDirect']);
            Route::prefix('conversations/{conversationId}')->group(function () {
                Route::get('/messages', [MessageController::class, 'messages']);
                Route::post('/messages', [MessageController::class, 'send']);
            });

            // Bildirimler
            Route::prefix('notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'index']);
                Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
                Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
                Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
                Route::delete('/{id}', [NotificationController::class, 'destroy']);
            });

            // =============================================
            // ADMIN 
            // =============================================

            Route::prefix('admin')
                ->middleware(['auth:api', 'super_admin'])
                ->group(function () {

                    // Paket Yönetimi
                    Route::prefix('packages')->group(function () {
                        Route::get('/', [AdminPackageController::class, 'index']);
                        Route::post('/', [AdminPackageController::class, 'store']);
                        Route::get('/{id}', [AdminPackageController::class, 'show']);
                        Route::put('/{id}', [AdminPackageController::class, 'update']);
                        Route::delete('/{id}', [AdminPackageController::class, 'destroy']);

                        Route::get('/{packageId}/limits', [AdminPackageLimitController::class, 'index']);
                        Route::post('/{packageId}/limits', [AdminPackageLimitController::class, 'store']);
                    });

                    // Limit Yönetimi
                    Route::prefix('limits')->group(function () {
                        Route::get('/compare', [AdminPackageLimitController::class, 'compare']);
                        Route::post('/bulk', [AdminPackageLimitController::class, 'bulkStore']);
                        Route::put('/{id}', [AdminPackageLimitController::class, 'update']);
                        Route::delete('/{id}', [AdminPackageLimitController::class, 'destroy']);
                    });

                    // Kullanıcı Yönetimi
                    Route::prefix('users')->group(function () {
                        Route::get('/', [AdminUserController::class, 'index']);
                        Route::get('/{id}', [AdminUserController::class, 'show']);
                        Route::put('/{id}/toggle-active', [AdminUserController::class, 'toggleActive']);
                        Route::post('/{id}/assign-role', [AdminUserController::class, 'assignRole']);
                        Route::post('/{id}/remove-role', [AdminUserController::class, 'removeRole']);

                        Route::get('/{userId}/membership', [AdminMembershipController::class, 'show']);
                        Route::put('/{userId}/membership', [AdminMembershipController::class, 'update']);
                        Route::get('/{userId}/membership-history', [AdminMembershipController::class, 'history']);
                    });
                });
        });
