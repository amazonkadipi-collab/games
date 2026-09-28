import type { MetadataRoute } from "next";

export default function sitemap(): MetadataRoute.Sitemap {
  const categories = ["action","adventure","arcade","racing","sports","puzzle","shooting","strategy","multiplayer","2-player","io-games","skill","horror","zombie"];
  return [
    { url: "https://games.example.com/", lastModified: new Date() },
    { url: "https://games.example.com/games", lastModified: new Date() },
    { url: "https://games.example.com/categories", lastModified: new Date() },
    ...categories.map(slug => ({ url: `https://games.example.com/category/${slug}`, lastModified: new Date() }))
  ];
}