import type { MetadataRoute } from "next";

function getSiteUrl() {
  return process.env.NEXT_PUBLIC_SITE_URL
    ? process.env.NEXT_PUBLIC_SITE_URL
    : `https://${process.env.VERCEL_PROJECT_PRODUCTION_URL || process.env.VERCEL_URL || "localhost:3000"}`;
}

export default function sitemap(): MetadataRoute.Sitemap {
  const siteUrl = getSiteUrl().replace(/\/$/, "");
  const categories = [
    "action",
    "adventure",
    "arcade",
    "racing",
    "sports",
    "puzzle",
    "shooting",
    "strategy",
    "multiplayer",
    "2-player",
    "io-games",
    "skill",
    "horror",
    "zombie",
  ];

  return [
    { url: `${siteUrl}/`, lastModified: new Date() },
    { url: `${siteUrl}/games`, lastModified: new Date() },
    { url: `${siteUrl}/categories`, lastModified: new Date() },
    ...categories.map((slug) => ({
      url: `${siteUrl}/category/${slug}`,
      lastModified: new Date(),
    })),
  ];
}