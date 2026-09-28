import type { Metadata } from "next";

type Props = { params: Promise<{ slug: string }> };

export async function generateMetadata({params}: Props): Promise<Metadata> {
  const {slug} = await params;
  const title = slug.split("-").map(x => x.charAt(0).toUpperCase()+x.slice(1)).join(" ");
  return {
    title: `${title} - Play Online Free`,
    description: `Play ${title} online for free in your browser. Discover controls, gameplay details and similar games.`,
    alternates: { canonical: `/game/${slug}` }
  };
}

export default async function GamePage({params}: Props) {
  const {slug} = await params;
  const title = slug.split("-").map(x => x.charAt(0).toUpperCase()+x.slice(1)).join(" ");
  return (
    <main className="container" style={{padding:"28px 0 70px"}}>
      <a href="/" style={{color:"var(--muted)",fontSize:13}}>← Home</a>
      <h1 style={{fontSize:"clamp(28px,4vw,44px)",margin:"24px 0 8px"}}>{title}</h1>
      <p style={{color:"var(--muted)",lineHeight:1.7}}>Play {title} online for free.</p>
      <div style={{marginTop:24,border:"1px solid var(--border)",borderRadius:16,background:"#050609",minHeight:520,display:"grid",placeItems:"center",color:"var(--muted)"}}>
        Game player will be connected to the GameMonetize feed here.
      </div>
      <section style={{maxWidth:850,marginTop:28}}>
        <h2>About {title}</h2>
        <p style={{color:"var(--muted)",lineHeight:1.8}}>Play this browser game instantly on desktop and mobile. The final page will be populated from the game catalog with the official title, thumbnail, category, instructions and playable embed.</p>
      </section>
    </main>
  );
}