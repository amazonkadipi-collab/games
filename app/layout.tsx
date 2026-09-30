import type { Metadata } from "next";
import type { ReactNode } from "react";
import "./globals.css";

const siteUrl = process.env.NEXT_PUBLIC_SITE_URL
  ? new URL(process.env.NEXT_PUBLIC_SITE_URL)
  : new URL(`https://${process.env.VERCEL_PROJECT_PRODUCTION_URL || process.env.VERCEL_URL || "localhost:3000"}`);

export const metadata: Metadata = {
  metadataBase: siteUrl,
  title: { default: "Free Online Games - Play Games Instantly", template: "%s | Free Online Games" },
  description: "Play free online games instantly in your browser. Discover action, racing, puzzle, sports, multiplayer and more HTML5 games.",
  keywords: ["free online games", "browser games", "HTML5 games", "play games online", "games"],
  robots: { index: true, follow: true },
  alternates: { canonical: "/" },
  openGraph: {
    type: "website",
    title: "Free Online Games - Play Games Instantly",
    description: "Discover and play free browser games instantly.",
    siteName: "Games"
  }
};

export default function RootLayout({ children }: Readonly<{ children: ReactNode }>) {
  return <html lang="en"><body>{children}</body></html>;
}
