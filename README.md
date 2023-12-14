<a name="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://github.com/terry-goldenowl/personal-finance-manager-be">
    <img src="/public/images/logo-money-master.png" alt="Logo" width="180" height="180">
  </a>

  <h3 align="center">MONEY MASTER</h3>

  <p align="center">
    Help you master your money effectively!
    <br />
    <br />
    <a href="https://money-master-nine.vercel.app/">View Demo</a>
    ·
    <a href="https://github.com/terry-goldenowl/personal-finance-manager-fe/issues">Report Bug</a>
    ·
    <a href="https://github.com/terry-goldenowl/personal-finance-manager-fe/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
        <li><a href="#project-folder-structure">Project Folder Structure</a></li>
        <li><a href="#architecture-diagram">Architecture Diagram</a></li>
        <li><a href="#db-diagram">DB Diagram</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project

Welcome to our Personal Finance Manager, your trusted companion for achieving financial wellness. Our website is designed with one primary goal in mind: to empower you to take control of your finances, make informed decisions, and secure your financial future. 

With our user-friendly platform, you can effortlessly track your income, expenses, and savings in one convenient place. We offer insightful budgeting tools, customizable spending categories, and robust reporting features that provide a clear overview of your financial health. Stay organized with our intuitive transaction categorization and easily set savings goals. 

<p align="right">(<a href="#readme-top">back to top</a>)</p>



### Built With

This section should list any major frameworks/libraries used to bootstrap your project. Leave any add-ons/plugins for the acknowledgements section. Here are a few examples.

* React
* Laravel
* TailwindCSS
* MySQL

<p align="right">(<a href="#readme-top">back to top</a>)</p>


### Project Folder Structure

```
.
├───app
│   ├───Console
│   ├───Exceptions
│   ├───Exports
│   ├───Http
│   │   ├───Controllers
│   │   │   ├───Api
│   │   │   │   └───v1
│   │   │   └───Web
│   │   ├───Helpers
│   │   ├───Middleware
│   │   ├───Requests
│   │   │   ├───Categories
│   │   │   ├───Plans
│   │   │   ├───Transactions
│   │   │   ├───Users
│   │   │   └───Wallets
│   │   └───Services
│   ├───Mail
│   ├───Models
│   ├───Providers
│   └───Services
├───bootstrap
│   └───cache
├───config
├───database
│   ├───factories
│   ├───migrations
│   └───seeders
├───public
│   ├───images
│   └───storage
│       └───images
│           ├───categories
│           ├───sample
│           ├───transactions
│           ├───users
│           └───wallets
├───resources
│   ├───css
│   ├───js
│   └───views
│       ├───emails
│       └───password
├───routes
├───storage
│   ├───app
│   │   └───public
│   │       └───images
│   │           ├───categories
│   │           ├───sample
│   │           ├───transactions
│   │           ├───users
│   │           └───wallets
│   ├───framework
│   │   ├───cache
│   │   │   ├───data
│   │   │   │   ├───02
│   │   │   │   │   └───47
│   │   │   │   └───...
│   │   │   └───laravel-excel
│   │   ├───sessions
│   │   ├───testing
│   │   └───views
│   └───logs
└───tests
    ├───Feature
    └───Unit
```

### Architecture Diagram
![image](https://github.com/terry-goldenowl/personal-finance-manager-be/assets/138744655/9f09766f-bcca-4fe7-acf0-5297010f8a80)

### DB Diagram
![mermaid-diagram-2023-10-09-181023](https://github.com/terry-goldenowl/personal-finance-manager-be/assets/138744655/235d0e74-5e73-4a6f-bf20-82449b0499f3)

<!-- GETTING STARTED -->
## Getting Started

This is the steps to guild you how to run the project locally:

### Prerequisites

Install composer (if not yet):
* Follow this page to know how to install composer https://getcomposer.org/download/

### Installation

Here's how you can install the repository and run project locally:

1. Clone the repo
   ```sh
   git clone https://github.com/terry-goldenowl/personal-finance-manager-be
   ```
2. Switch to the repo folder
   ```sh
   cd personal-finance-manager-be
   ``` 
3. Install all the dependencies using composer
   ```sh
   composer install
   ```
4. Copy the example env file and make the required configuration changes in the .env file
   ```sh
   cp .env.example .env
   ```
5. Generate app key
   ```sh
   php artisan key:generate
   ```
6. Run the database migrations
   ```sh
   php artisan migrate
   ```
7. Seed data
   ```sh
   php artisan db:seed
   ```
8. Start the local development server
   ```sh
   php artisan serve
   ```
9. Make the jobs queue works
   ```sh
   php artisan queue:work
   ```
You can now access the server at http://localhost:8000

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- USAGE EXAMPLES -->
## Usage

* User role:
  * Create your account (must verify email) and login
  * Set up your first wallet & manage your wallets
  * Make and manage transactions
  * Make and manage plans for month and categories of a month
  * See reports
  * Make manage your own categories
  * Make and manage goals and add additions to goal

* Admin role:
  * See some statistics
  * See list of users and delete users
  * Manage default categories
 
* Both roles
  * Login & logout
  * Manage profile

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/othneildrew/Best-README-Template.svg?style=for-the-badge
[contributors-url]: https://github.com/othneildrew/Best-README-Template/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/othneildrew/Best-README-Template.svg?style=for-the-badge
[forks-url]: https://github.com/othneildrew/Best-README-Template/network/members
[stars-shield]: https://img.shields.io/github/stars/othneildrew/Best-README-Template.svg?style=for-the-badge
[stars-url]: https://github.com/othneildrew/Best-README-Template/stargazers
[issues-shield]: https://img.shields.io/github/issues/othneildrew/Best-README-Template.svg?style=for-the-badge
[issues-url]: https://github.com/othneildrew/Best-README-Template/issues
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/othneildrew/Best-README-Template/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/othneildrew
[product-screenshot]: images/screenshot.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 
