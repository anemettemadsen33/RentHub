"use client";
import Link from "next/link";
import Image from "next/image";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Home, Plus, Eye, Star, Settings } from "lucide-react";

export interface PropertyItem {
  id: number;
  title: string;
  description: string;
  images: string[];
  status: "active" | "inactive";
  views: number;
  rating: number;
}

interface Props {
  properties: PropertyItem[];
}

export function HostPropertiesGrid({ properties }: Props) {
  if (!properties || properties.length === 0) {
    return (
      <Card>
        <CardContent className="flex flex-col items-center justify-center py-12">
          <Home className="h-12 w-12 text-muted-foreground mb-4" />
          <h3 className="text-lg font-semibold mb-2">No properties yet</h3>
            <p className="text-muted-foreground mb-4">Start by adding your first property</p>
          <Button asChild>
            <Link href="/host/properties/new">
              <Plus className="mr-2 h-4 w-4" />
              Add Your First Property
            </Link>
          </Button>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      {properties.map((property) => (
        <Card key={property.id} className="overflow-hidden">
          <div className="aspect-video bg-gray-200 relative">
            {property.images?.[0] && (
              <Image
                src={property.images[0]}
                alt={property.title}
                fill
                sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                className="object-cover"
                priority={false}
              />
            )}
            <div className="absolute top-2 right-2">
              {property.status === "active" ? (
                <span className="bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">Active</span>
              ) : (
                <span className="bg-gray-500 text-white px-2 py-1 rounded text-xs font-medium">Inactive</span>
              )}
            </div>
          </div>
          <CardHeader>
            <CardTitle className="line-clamp-1">{property.title}</CardTitle>
            <CardDescription className="line-clamp-2">{property.description}</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center gap-1 text-sm">
                <Eye className="h-4 w-4" />{property.views || 0} views
              </div>
              <div className="flex items-center gap-1 text-sm">
                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />{property.rating || "0.0"}
              </div>
            </div>
            <div className="flex gap-2">
              <Button asChild variant="outline" size="sm" className="flex-1">
                <Link href={`/host/properties/${property.id}`}>
                  <Settings className="mr-2 h-4 w-4" />Manage
                </Link>
              </Button>
              <Button asChild variant="outline" size="sm" className="flex-1">
                <Link href={`/properties/${property.id}`}>
                  <Eye className="mr-2 h-4 w-4" />View
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
