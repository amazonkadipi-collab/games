import { getCatalog } from "@/lib/catalog";
const fallback=["Action","Adventure","Arcade","Racing","Sports","Puzzle","Shooting","Strategy","Multiplayer","2 Player",".io Games","Skill","Horror","Zombie","Driving","Fighting","Simulation","Idle","Platform","Stickman","Football","Basketball","Card Games","Board Games","Cooking","Dress Up","Tower Defense"];
const slugify=(s:string)=>s.toLowerCase().replaceAll(".","").replaceAll(" ","-");
export default async function CategoriesPage(){
 const games=await getCatalog();
 const derived=Array.from(new Set(games.flatMap(g=>[g.category,...g.tags]).filter(Boolean))).slice(0,80) as string[];
 const categories=derived.length?derived:fallback;
 return <main className="container" style={{padding:"42px 0 70px"}}>
  <a href="/" style={{color:"var(--muted)",fontSize:13}}>← Home</a>
  <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 10px"}}>Game Categories</h1>
  <p style={{color:"var(--muted)",maxWidth:760,lineHeight:1.7}}>Browse free online games by genre, gameplay style and theme{games.length?" · "+games.length+" games in the live catalog":""}.</p>
  <div className="categories" style={{marginTop:28}}>{categories.map(c=><a className="category" key={c} href={"/category/"+slugify(c)}>{c} Games</a>)}</div>
 </main>
}