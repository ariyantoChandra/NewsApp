import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})

export class HttpService {

  constructor(private http: HttpClient) {}

  get<T>(url: string, params?: any, headers?: HttpHeaders): Observable<T> {
    console.log("Processing HTTP GET Request");
    return this.http.get<T>(url, {
      params: new HttpParams({ fromObject: params }),
      headers
    });
  }

  post<T>(url: string, body: any, headers?: HttpHeaders): Observable<T> {
    console.log("Processing HTTP POST Request");
    return this.http.post<T>(url, body, { headers });
  }

  put<T>(url: string, body: any, headers?: HttpHeaders): Observable<T> {
    console.log("Processing HTTP PUT Request");
    return this.http.put<T>(url, body, { headers });
  }

  delete<T>(url: string, params?: any, headers?: HttpHeaders): Observable<T> {
    console.log("Processing HTTP DELETE Request");
    return this.http.delete<T>(url, {
      params: new HttpParams({ fromObject: params }),
      headers
    });
  }
}
