import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { NewsAPI, Category } from '../interface';
import { HttpService } from '../http-service';



@Component({
  selector: 'app-home',
  templateUrl: 'home.page.html',
  styleUrls: ['home.page.scss'],
  standalone: false,
})
export class HomePage {
  categories: Category[] = [];
  breakingNews: NewsAPI | undefined;
  recommendations: NewsAPI[] = [];

  constructor(private router: Router, private http: HttpService) {}
  ngOnInit() {}
  ionViewWillEnter() {
    this.loadNews();
    this.loadCategories();
  }
  loadCategories() {
    this.http.get_categories().subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.categories = res.data;
        }
      },
      (err) => {
        console.error('Gagal ambil kategori', err);
      }
    );
  }
  loadNews() {
    this.http.get_news().subscribe(
      (res: any) => {
        if (res.status === 'success') {
          const allNews: NewsAPI[] = res.data;

          if (allNews.length > 0) {
            this.breakingNews = allNews[0];
            this.recommendations = allNews.slice(1);
          }
        }
      },
      (error) => {
        console.error('Error fetching news:', error);
      }
    );
  }
  goToNewsList(categoryId: number, categoryName: string) {
    this.router.navigate(['/news-list', categoryId, categoryName]);
  }
}
