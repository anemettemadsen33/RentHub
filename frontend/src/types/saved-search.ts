export interface SavedSearch {
  id: number;
  user_id: number;
  name: string;
  location?: string;
  latitude?: number;
  longitude?: number;
  radius_km?: number;
  min_price?: number;
  max_price?: number;
  min_bedrooms?: number;
  max_bedrooms?: number;
  min_bathrooms?: number;
  max_bathrooms?: number;
  min_guests?: number;
  property_type?: string;
  amenities?: number[];
  check_in?: string;
  check_out?: string;
  enable_alerts: boolean;
  alert_frequency: 'instant' | 'daily' | 'weekly';
  last_alert_sent_at?: string;
  new_listings_count: number;
  is_active: boolean;
  search_count: number;
  last_searched_at?: string;
  created_at: string;
  updated_at: string;
}

export interface SavedSearchFormData {
  name: string;
  location?: string;
  latitude?: number;
  longitude?: number;
  radius_km?: number;
  min_price?: number;
  max_price?: number;
  min_bedrooms?: number;
  max_bedrooms?: number;
  min_bathrooms?: number;
  max_bathrooms?: number;
  min_guests?: number;
  property_type?: string;
  amenities?: number[];
  check_in?: string;
  check_out?: string;
  enable_alerts?: boolean;
  alert_frequency?: 'instant' | 'daily' | 'weekly';
}

export interface SavedSearchStatistics {
  total_searches: number;
  active_searches: number;
  with_alerts: number;
  most_used: SavedSearch[];
  recent: SavedSearch[];
}
