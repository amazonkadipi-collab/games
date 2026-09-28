const categories = ["Action","Adventure","Arcade","Racing","Sports","Puzzle","Shooting","Strategy","Multiplayer","2 Player",".io Games","Skill","Horror","Zombie","Girls & Lifestyle","Educational","Driving","Fighting","Simulation","Idle","Platform","Stickman","Football","Basketball","Card Games","Board Games","Cooking","Dress Up","Tower Defense"];

export default function CategoriesPage(){
  return <main className="container" style={{padding:"42px 0 70px"}}>
    <a href="/" style={{color:"var(--muted)",fontSize:13}}>← Home</a>
    <h1 style={{fontSize:"clamp(30px,4vw,48px)",margin:"22px 0 10px"}}>Game Categories</h1>
    <p style={{color:"var(--muted)",maxWidth:760,lineHeight:1.7}}>Browse free online games by genre, gameplay style and theme.</p>
    <div className="categories" style={{marginTop:28}}>{categories.map(c=><a className="category" key={c} href={"/category/"+c.toLowerCase().replaceAll(" ","-")}>{c} Games</a>)}</div>
  </main>
}