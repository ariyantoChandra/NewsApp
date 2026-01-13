import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpService } from './http-service';

@Component({
  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
  standalone: false,
})
export class AppComponent {
  
  user: any = {};

  constructor(private router: Router, private httpService: HttpService) {
    this.checkLoginStatus();
    this.listenToLoginEvents();
  }
  checkLoginStatus() {
    const data = localStorage.getItem('user_data');
    if (data) {
      this.user = JSON.parse(data);
    } else {
      this.router.navigate(['/login']);
    }
  }
  listenToLoginEvents() {
    this.httpService.userSubject.subscribe((userData) => {
      this.user = userData;
    });
  }
  onLogout() {
    localStorage.clear();
    this.router.navigate(['/login']);
  }
}

