import { Component, OnInit } from '@angular/core';
import { HttpService } from '../http-service';
import { Router } from '@angular/router';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-create-news',
  templateUrl: './create-news.page.html',
  styleUrls: ['./create-news.page.scss'],
  standalone:false
})
export class CreateNewsPage implements OnInit {
  title: string = '';
  content: string = '';
  categoryId: any;
  tags: string = '';
  selectedFiles: File[] = [];
  categories: any[] = [];

  constructor(
    private httpService: HttpService,
    private router: Router,
    private toastCtrl: ToastController
  ) {}

  ngOnInit() {
    this.loadCategories();
  }

  loadCategories() {
    this.httpService.get_categories().subscribe((res: any) => {
      if (res.status === 'success') {
        this.categories = res.data;
      }
    });
  }

  onFileChange(event: any) {
    if (event.target.files && event.target.files.length > 0) {
      this.selectedFiles = Array.from(event.target.files);
    }
  }

  async submitNews() {
    if (!this.title || !this.content || !this.categoryId) {
      this.showToast('Judul, Konten, dan Kategori wajib diisi');
      return;
    }
    if (this.selectedFiles.length < 4) {
      this.showToast('Minimal upload 4 gambar');
      return;
    }

    const userJson = localStorage.getItem('user_data');
    if (!userJson) {
      this.router.navigate(['/login']);
      return;
    }
    const user = JSON.parse(userJson);
    const writerUsername = user.username;

    const formData = new FormData();
    formData.append('title', this.title);
    formData.append('content', this.content);
    formData.append('category_id', this.categoryId);
    formData.append('writer_username', writerUsername);
    formData.append('city_id', '1');
    formData.append('tags', this.tags);

    for (let i = 0; i < this.selectedFiles.length; i++) {
      formData.append('images[]', this.selectedFiles[i]);
    }

    this.httpService.createNews(formData).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.showToast('Berita berhasil dibuat!');
          this.router.navigate(['/home']);
        } else {
          this.showToast('Gagal: ' + res.message);
        }
      },
      (err) => {
        this.showToast('Error upload berita: ' + JSON.stringify(err));
      }
    );
  }

  async showToast(msg: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000,
    });
    toast.present();
  }
}
