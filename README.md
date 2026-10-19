## FUTURE TECH STACK
The stack I'd use is Laravel as an API (with Sanctum for login tokens), MySQL on the server, and a React Native app built with Expo, with a local SQLite copy of the data on the phone. For offline use, the BHW taps "Download my residents" while online. The app saves those profiles in SQLite, and anything entered offline goes into a local queue. When the internet returns, the app sends the queue to Laravel. Android and iOS both come from the one React Native codebase. Android is easy to test. On iOS you can test for free through the Expo Go app, but releasing it needs an Apple Developer account at about $99 a year, so treat iOS as optional for the timeframe. The database is MySQL, which is the schema you have now, plus SQLite on each phone. Laravel is a good choice, but "Laravel and React" means React Native for a mobile app, not the web version of React. React Native suits you because you already know JavaScript. Flutter is the main alternative, but it means learning Dart.

Two things in the current project won't carry over. Your Blade pages are server-rendered, so they can't work offline. They become the design reference, and the mobile app is a new front end that talks to a Laravel API. Also, the schema I wrote uses auto-increment IDs, which collide when two phones create records offline. Add a `uuid` column (a unique ID the phone creates itself) to every table that can be filled offline, such as vital signs, medications, scores, and photos. Build it in three stages: the online app first, then offline download, then offline entry with sync, which is the hardest part. Do you want me to write the SQL that adds those `uuid` columns to your schema?


 <div class="rounded-xl bg-white p-4 shadow">IT DEPENDS WHAT ACCOUNT IS SIGNED IN. 
        <br><br>TEMPORARY UI PAGE.
        <br><br> PLEASE CONFIGURE THE LINK TO NAVIGATE IN PAGE
        <br><br> /bhw/dashboard
        <br><br> /bhw/events/create
        <br><br> /bhw/referrals
        <br><br> /bhw/profile
        <br><br> /bhw/events/1/edit
        
        <br><br> /register
        <br><br> /login
        <br><br> /login

        <br><br> /resident/home
        

    </div>














<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
