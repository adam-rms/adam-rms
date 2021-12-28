import { createContext, useState } from "react";
import Api from "../../utilities/Api";

// The actual context
export const ProjectDataContext = createContext<any>(null);

//Create a provider wrapper to make the interaction with the context easier
const ProjectDataProvider: React.FC<React.ReactNode> = ({ children }) => {
  //Create default state
  const [projectData, setProjectData] = useState<IProjectData>({
    project: {},
    files: [],
    assetsAssignmentsStatus: [],
    FINANCIALS: {},
  });

  /**
   * Refresh Context
   * Replace all projects in context
   */
  async function refreshProjectData(id: number) {
    setProjectData(await Api("projects/data.php", { id: id }));
  }

  // Don't forget to add new functions to the value of the provider!
  return (
    <ProjectDataContext.Provider value={{ projectData, refreshProjectData }}>
      {children}
    </ProjectDataContext.Provider>
  );
};

export default ProjectDataProvider;
