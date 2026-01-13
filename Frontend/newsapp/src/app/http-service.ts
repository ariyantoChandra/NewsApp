import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable, Subject } from 'rxjs';

const BASE_URL = 'http://localhost/NewsApp/Backend/api/';

@Injectable({
  providedIn: 'root',
})
export class HttpService {
  public userSubject = new Subject<any>();
  constructor(private http: HttpClient) {}

  get<T>(url: string, params?: any, headers?: HttpHeaders): Observable<T> {
    return this.http.get<T>(url, {
      params: new HttpParams({ fromObject: params }),
      headers,
    });
  }

  post<T>(url: string, body: any, headers?: HttpHeaders): Observable<T> {
    return this.http.post<T>(url, body, { headers });
  }

  put<T>(url: string, body: any, headers?: HttpHeaders): Observable<T> {
    console.log('Processing HTTP PUT Request');
    return this.http.put<T>(url, body, { headers });
  }

  delete<T>(url: string, params?: any, headers?: HttpHeaders): Observable<T> {
    console.log('Processing HTTP DELETE Request');
    return this.http.delete<T>(url, {
      params: new HttpParams({ fromObject: params }),
      headers,
    });
  }
  login_user(username_input: string, password_input: string): Observable<any> {
    const url = BASE_URL + 'login.php';
    const data = {
      username: username_input,
      password: password_input,
    };
    return this.post(url, data);
  }
  register_user(userData: any): Observable<any> {
    const url = BASE_URL + 'register.php';
    return this.post(url, userData);
  }
  register_writer(userData: any): Observable<any> {
    const url = BASE_URL + 'register_writer.php';
    return this.post(url, userData);
  }

  createNews(formData: FormData) {
    const url = BASE_URL + 'create_news.php';
    return this.post(url, formData);
  }
  emitUserLogin(userData: any) {
    this.userSubject.next(userData);
  }

  //#region GET DATA
  getMedias() {
    const url = BASE_URL + 'get_media.php';
    return this.get(url);
  }
  get_categories(): Observable<any> {
    const url = BASE_URL + 'get_categories.php';
    return this.get(url);
  }

  get_countries(): Observable<any> {
    const url = BASE_URL + 'get_countries.php';
    return this.get(url);
  }
  get_news(): Observable<any> {
    const url = BASE_URL + 'get_news.php';
    return this.get(url);
  }

  get_news_detail(id: number, username: string = ''): Observable<any> {
    const url = BASE_URL + 'get_news_by_id.php';
    return this.get(url, { id: id, username: username });
  }
  get_news_by_category(
    categoryId: number,
    page: number = 1,
    limit: number = 10
  ): Observable<any> {
    const url = BASE_URL + 'get_news_by_category.php';
    return this.get(url, { category_id: categoryId, page: page, limit: limit });
  }
  //#endregion
  //#region CREATE
  create_category(categoryData: any): Observable<any> {
    const url = BASE_URL + 'create_category.php';
    return this.post(url, categoryData);
  }
  create_news(newsData: FormData): Observable<any> {
    const url = BASE_URL + 'create_news.php';
    return this.post(url, newsData);
  }
  //#endregion
  //#region LIKE, VIEW, DELETE

  add_view(newsId: number): Observable<any> {
    const url = BASE_URL + 'view_count.php';
    return this.post(url, { news_id: newsId });
  }

  like_news(newsId: number, username: string): Observable<any> {
    const url = BASE_URL + 'like_news.php';
    return this.post(url, { news_id: newsId, username: username });
  }

  dislike_news(newsId: number, username: string): Observable<any> {
    const url = BASE_URL + 'dislike_news.php';
    return this.post(url, { news_id: newsId, username: username });
  }
  delete_news(newsId: number): Observable<any> {
    const url = BASE_URL + 'delete_news.php';
    return this.post(url, { news_id: newsId });
  }
  //#endregion
}
