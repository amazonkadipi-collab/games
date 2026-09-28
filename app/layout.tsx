import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  metadataBase: new URL("https://games.example.com"),
  title: {
    default: "Free Online Games - Play Games Instantly",
    template: "%s | Free Online Games"
  },
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

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}