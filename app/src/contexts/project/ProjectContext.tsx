import { createContext, useState } from "react";
import Api from "../../utilities/Api";

// The actual context
export const ProjectContext = createContext<any>(null);

//Create a provider wrapper to make the interaction with the context easier
const ProjectProvider: React.FC<React.ReactNode> = ({ children }) => {
  //Create default state
  const [projects, setProjects] = useState<IProject[]>([]);

  /**
   * Refresh Context
   * Replace all projects in context
   */
  async function refreshProjects() {
    setProjects(await Api("projects/list.php"));
  }

  // Don't forget to add new functions to the value of the provider!
  return (
    <ProjectContext.Provider value={{ projects, refreshProjects }}>
      {children}
    </ProjectContext.Provider>
  );
};

export default ProjectProvider;
