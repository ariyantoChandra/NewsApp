import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { HttpService } from '../http-service';
import { ToastController } from '@ionic/angular';

@Component({
  selector: 'app-news-detail',
  templateUrl: './news-detail.page.html',
  styleUrls: ['./news-detail.page.scss'],
  standalone: false,
})
export class NewsDetailPage implements OnInit {
  article: any = null;
  selectedImage: string = '';
  userRating: number = 0;
  newComment: string = '';
  currentUser: any = {};

  constructor(
    private route: ActivatedRoute,
    private http: HttpService,
    private toastCtrl: ToastController
  ) {}

  ngOnInit() {
    const userStr = localStorage.getItem('user_data');
    if (userStr) {
      this.currentUser = JSON.parse(userStr);
    }
    const newsId = this.route.snapshot.paramMap.get('id');
    if (newsId) {
      this.loadNewsDetail(+newsId);
      this.addViewCount(+newsId);
    }
  }
  loadNewsDetail(id: number, username: string = '') {
    const user =
      username ||
      (this.currentUser && this.currentUser.username
        ? this.currentUser.username
        : '');
    this.http.get_news_detail(id, user).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          const data = res.data;

          this.article = {
            id: data.id,
            title: data.title,
            mainImage:
              data.images && data.images.length > 0
                ? data.images[0]
                : 'assets/images/placeholder.jpg',
            images: data.images || [],
            categories: [data.category],
            author: data.author.fullname,
            date: data.created_at,
            content: data.content,
            user_status: Number(data.user_status || 0),
            dislike_count: Number(data.dislike_count || 0),
            view_count: data.view_count || 0,
            like_count: Number(data.like_count || 0),
            comment_count: data.comment_count || 0,
            tags: data.tags || [],
            comments: data.comments || [],
            rating: data.rating,
            isFavorite: false,
          };

          this.selectedImage = this.article.mainImage;
        } else {
          this.showToast('Gagal memuat berita', 'danger');
        }
      },
      (err) => {
        console.error(err);
      }
    );
  }
  addViewCount(id: number) {
    this.http.add_view(id).subscribe();
  }

  selectImage(img: string) {
    this.selectedImage = img;
  }

  rateNews(star: number) {
    this.userRating = star;
    this.showToast(`Anda memberi rating ${star} bintang`, 'primary');
  }

  addComment() {
    if (!this.newComment.trim()) return;
    const mockComment = {
      user: { name: 'Saya' },
      text: this.newComment,
    };

    if (!this.article.comments) this.article.comments = [];
    this.article.comments.push(mockComment);

    this.newComment = '';
    this.showToast('Komentar terkirim', 'success');
  }

  async showToast(msg: string, color: string) {
    const toast = await this.toastCtrl.create({
      message: msg,
      duration: 2000,
      color: color,
      position: 'bottom',
    });
    toast.present();
  }

  toggleLike() {
    if (!this.article || !this.currentUser.username) {
      this.showToast('Silahkan login terlebih dahulu', 'warning');
      return;
    }

    this.http.like_news(this.article.id, this.currentUser.username).subscribe(
      (res: any) => {
        if (res.status === 'success') {
          this.article.user_status = Number(res.data.user_status);
          if (res.data.stats) {
            this.article.like_count = Number(res.data.stats.like_count || 0);
            this.article.dislike_count = Number(
              res.data.stats.dislike_count || 0
            );
          }

          const msg =
            this.article.user_status === 1
              ? 'Berita disukai.'
              : 'Like dibatalkan.';
          this.showToast(msg, 'success');
        }
      },
      (err) => {
        console.error('Gagal like', err);
        this.showToast('Writter tidak bisa melakukan aksi tersebut', 'danger');
      }
    );
  }

  toggleDislike() {
    if (!this.article || !this.currentUser.username) {
      this.showToast('Silahkan login terlebih dahulu', 'warning');
      return;
    }

    this.http
      .dislike_news(this.article.id, this.currentUser.username)
      .subscribe(
        (res: any) => {
          if (res.status === 'success') {
            this.article.user_status = Number(res.data.user_status);
            if (res.data.stats) {
              this.article.like_count = Number(res.data.stats.like_count || 0);
              this.article.dislike_count = Number(
                res.data.stats.dislike_count || 0
              );
            }
            const msg =
              this.article.user_status === 0
                ? 'Berita di-dislike.'
                : 'Dislike dibatalkan.';
            this.showToast(msg, 'primary');
          }
        },
        (err) => {
          console.error('Gagal dislike', err);
          this.showToast('Writter tidak bisa melakukan aksi tersebut', 'danger');
        }
      );
  }
}
