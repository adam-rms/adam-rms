import './ExploreContainer.css';
import AssetTypeList from './assets/AssetTypes';

interface ContainerProps {
  name: string;
}

const ExploreContainer: React.FC<ContainerProps> = ({ name }) => {
  return (
    <div className="container">
      <strong>{name}</strong>
      <AssetTypeList></AssetTypeList>
    </div>
  );
};

export default ExploreContainer;
