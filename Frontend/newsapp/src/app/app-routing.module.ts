import { NgModule } from '@angular/core';
import { PreloadAllModules, RouterModule, Routes } from '@angular/router';

const routes: Routes = [
  {
    path: 'home',
    loadChildren: () =>
      import('./home/home.module').then((m) => m.HomePageModule),
  },
  {
    // 1. Tambahkan rute untuk halaman login
    path: 'login',
    loadChildren: () =>
      import('./login/login.module').then((m) => m.LoginPageModule),
  },
  {
    path: '',
    redirectTo: 'login',
    pathMatch: 'full',
  },
  {
    path: 'register',
    loadChildren: () =>
      import('./register/register.module').then((m) => m.RegisterPageModule),
  },
  {
    path: 'create-category',
    loadChildren: () =>
      import('./create-category/create-category.module').then(
        (m) => m.CreateCategoryPageModule
      ),
  },
  {
    path: 'create-news',
    loadChildren: () =>
      import('./create-news/create-news.module').then(
        (m) => m.CreateNewsPageModule
      ),
  },
  {
    path: 'news-list',
    loadChildren: () =>
      import('./news-list/news-list.module').then((m) => m.NewsListPageModule),
  },
  {
    path: 'news-detail/:id',
    loadChildren: () =>
      import('./news-detail/news-detail.module').then(
        (m) => m.NewsDetailPageModule
      ),
  },
  {
    path: 'favorites',
    loadChildren: () =>
      import('./favorites/favorites.module').then((m) => m.FavoritesPageModule),
  },
  {
    path: 'search',
    loadChildren: () =>
      import('./search/search.module').then((m) => m.SearchPageModule),
  },
  {
    path: 'register-writer',
    loadChildren: () => import('./register-writer/register-writer.module').then( m => m.RegisterWriterPageModule)
  },
];

@NgModule({
  imports: [
    RouterModule.forRoot(routes, { preloadingStrategy: PreloadAllModules })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
