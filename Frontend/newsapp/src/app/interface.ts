export interface NewsAPI {
  id: number;
  title: string;
  slug: string;
  content: string;
  images: string[];
  category: string;
  view_count: number;
  like_count: number;
  rating: number;
  tags: string[];
  author: {
    username: string;
    fullname: string;
    email: string;
    role: string;
    profile_picture_address: string;
  };
  created_at: string;
}

export interface Category {
  id: number;
  name: string;
  icon?: string;
}
