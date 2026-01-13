import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { HttpService } from '../http-service';
import { AlertController, ToastController } from '@ionic/angular';

@Component({
  selector: 'app-news-list',
  templateUrl: './news-list.page.html',
  styleUrls: ['./news-list.page.scss'],
  standalone: false,
})
export class NewsListPage implements OnInit {
  newsList: any[] = [];
  pageTitle: string = 'Daftar Berita';
  isMyNewsMode: boolean = false; 
  currentUser: any = {};

  constructor(
    private route: ActivatedRoute,
    private http: HttpService,
    private router: Router,
    private alertCtrl: AlertController,
    private toastCtrl: ToastController
  ) {}

  ngOnInit() {
    const userStr = localStorage.getItem('user_data');
    if (userStr) this.currentUser = JSON.parse(userStr);
  }

  ionViewWillEnter() {
    // Cek parameter dari URL
    const params = this.route.snapshot.params;

    if (params['mode'] === 'my-news') {
      // MODE: BERITA SAYA
      this.pageTitle = 'Berita Saya';
      this.isMyNewsMode = true;
      this.loadMyNews();
    } else if (params['id']) {
      // MODE: KATEGORI
      this.pageTitle = params['name'] || 'Kategori';
      this.isMyNewsMode = false;
      this.loadNewsByCategory(+params['id']);
    } else {
      // Default: Semua Berita
      this.pageTitle = 'Semua Berita';
      this.isMyNewsMode = false;
      this.loadAllNews();
    }
  }

  loadAllNews() {
    this.http.get_news().subscribe((res: any) => {
      if (res.status === 'success') {
        this.newsList = res.data;
      }
    });
  }

  loadNewsByCategory(catId: number) {
    // Karena API get_news_by_category belum ada di file yang diupload,
    // kita filter manual dari get_news (IDEALNYA buat API khusus di backend)
    this.http.get_news().subscribe((res: any) => {
      if (res.status === 'success') {
        // Filter array di frontend
        this.newsList = res.data.filter(
          (news: any) => news.category_id == catId
        );
      }
    });
  }

  loadMyNews() {
    // Filter berita berdasarkan author.username / author.id
    this.http.get_news().subscribe((res: any) => {
      if (res.status === 'success') {
        this.newsList = res.data.filter(
          (news: any) => news.author.username === this.currentUser.username
        );
      }
    });
  }

  // Fungsi Hapus Berita
  async confirmDelete(newsId: number, event: Event) {
    event.stopPropagation(); // Biar tidak masuk ke halaman detail saat klik tombol hapus

    const alert = await this.alertCtrl.create({
      header: 'Konfirmasi',
      message: 'Apakah Anda yakin ingin menghapus berita ini?',
      buttons: [
        { text: 'Batal', role: 'cancel' },
        {
          text: 'Hapus',
          role: 'destructive',
          handler: () => {
            this.deleteNews(newsId);
          },
        },
      ],
    });
    await alert.present();
  }

  deleteNews(newsId: number) {
    this.http.delete_news(newsId).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          // Hapus dari list di layar
          this.newsList = this.newsList.filter((n) => n.id !== newsId);
          this.showToast('Berita berhasil dihapus', 'success');
        } else {
          this.showToast('Gagal menghapus: ' + res.message, 'danger');
        }
      },
      (err) => {
        console.error(err);
        this.showToast('Terjadi kesalahan sistem', 'danger');
      }
    );
  }

  async showToast(msg: string, color: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000,
      color: color,
    });
    toast.present();
  }
}
